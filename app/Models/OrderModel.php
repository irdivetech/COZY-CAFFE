<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class OrderModel extends Model
{
    private const ORDER_STATUSES = [
        'Pending',
        'Confirmed',
        'Preparing',
        'Ready',
        'Completed',
        'Cancelled',
    ];

    public function createFromCart(
        int $userId,
        string $orderType,
        string $bookingMode,
        ?int $bookingId,
        array $newBooking
    ): array {
        if (!in_array($orderType, ['Takeaway', 'Dine-in'], true)) {
            throw new InvalidArgumentException('Jenis pesanan tidak valid.');
        }

        $this->db->beginTransaction();
        try {
            $cartStatement = $this->db->prepare(
                'SELECT id FROM carts WHERE user_id = :user_id FOR UPDATE'
            );
            $cartStatement->execute(['user_id' => $userId]);
            $cartId = $cartStatement->fetchColumn();
            if ($cartId === false) {
                throw new DomainException('Keranjang kosong. Tambahkan menu sebelum checkout.');
            }

            $cart = (new CartModel())->getCartForCheckout($userId);
            if (!$cart['items']) {
                throw new DomainException('Keranjang kosong. Tambahkan menu sebelum checkout.');
            }
            if ($cart['has_unavailable_items']) {
                throw new DomainException('Hapus menu yang tidak tersedia sebelum checkout.');
            }
            if ($cart['total_cents'] > 9999999999 || $cart['subtotal_cents'] > 9999999999) {
                throw new DomainException('Total pesanan melebihi batas transaksi.');
            }

            $linkedBookingId = null;
            if ($orderType === 'Dine-in') {
                if ($bookingMode === 'existing') {
                    if ($bookingId === null || $bookingId < 1) {
                        throw new InvalidArgumentException('Pilih reservasi aktif untuk pesanan Dine-in.');
                    }

                    $bookingStatement = $this->db->prepare(
                        "SELECT id
                         FROM bookings
                         WHERE id = :id
                           AND user_id = :user_id
                           AND status = 'Confirmed'
                           AND booking_date >= CURRENT_DATE
                         FOR UPDATE"
                    );
                    $bookingStatement->execute([
                        'id' => $bookingId,
                        'user_id' => $userId,
                    ]);
                    $linkedBookingId = $bookingStatement->fetchColumn();
                    if ($linkedBookingId === false) {
                        throw new DomainException('Reservasi tidak aktif atau bukan milik akun ini.');
                    }
                    $linkedBookingId = (int) $linkedBookingId;
                } elseif ($bookingMode === 'new') {
                    $linkedBookingId = (new BookingService())->createWithinTransaction(
                        $userId,
                        (string) ($newBooking['date'] ?? ''),
                        (string) ($newBooking['start_time'] ?? ''),
                        (int) ($newBooking['guest_count'] ?? 0),
                        (int) ($newBooking['table_id'] ?? 0),
                        isset($newBooking['notes']) && $newBooking['notes'] !== ''
                            ? (string) $newBooking['notes']
                            : null
                    );
                } else {
                    throw new InvalidArgumentException('Pilih reservasi aktif atau buat reservasi baru.');
                }
            }

            $amounts = [
                'subtotal' => $this->centsToDecimal($cart['subtotal_cents']),
                'discount' => $this->centsToDecimal($cart['discount_cents']),
                'total' => $this->centsToDecimal($cart['total_cents']),
            ];
            $order = $this->insertOrderWithUniqueCode(
                $userId,
                $linkedBookingId,
                $orderType,
                $amounts
            );
            NotificationHelper::notifyAdmins(
                'Pesanan baru',
                'Pesanan ' . $order['order_code'] . ' baru dibuat.',
                '/admin/orders'
            );

            $itemStatement = $this->db->prepare(
                'INSERT INTO order_items
                    (order_id, menu_id, menu_name_snapshot, price_snapshot, quantity, notes, subtotal)
                 VALUES
                    (:order_id, :menu_id, :menu_name_snapshot, :price_snapshot, :quantity, :notes, :subtotal)'
            );
            foreach ($cart['items'] as $item) {
                $itemStatement->execute([
                    'order_id' => $order['id'],
                    'menu_id' => $item['menu_id'],
                    'menu_name_snapshot' => $item['menu_name'],
                    'price_snapshot' => $item['price'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'],
                    'subtotal' => $this->centsToDecimal($item['line_subtotal_cents']),
                ]);
            }

            $paymentStatement = $this->db->prepare(
                "INSERT INTO payments (order_id, payment_method, amount, status)
                 VALUES (:order_id, 'Cash', :amount, 'Unpaid')"
            );
            $paymentStatement->execute([
                'order_id' => $order['id'],
                'amount' => $amounts['total'],
            ]);

            $clearCart = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
            $clearCart->execute(['cart_id' => $cartId]);

            $this->db->commit();

            return [
                'id' => $order['id'],
                'order_code' => $order['order_code'],
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function activeBookingsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            "SELECT b.id, b.booking_date, b.start_time, b.end_time, b.guest_count,
                    t.table_number
             FROM bookings b
             INNER JOIN tables t ON t.id = b.table_id
             WHERE b.user_id = :user_id
               AND b.status = 'Confirmed'
               AND b.booking_date >= CURRENT_DATE
             ORDER BY b.booking_date, b.start_time, b.id"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT o.id, o.order_code, o.order_type, o.subtotal, o.discount_amount,
                    o.total_amount, o.status, o.created_at, p.payment_method,
                    p.status AS payment_status, p.paid_at, b.booking_date,
                    b.start_time, b.end_time, t.table_number
             FROM orders o
             INNER JOIN payments p ON p.order_id = o.id
             LEFT JOIN bookings b ON b.id = o.booking_id
             LEFT JOIN tables t ON t.id = b.table_id
             WHERE o.user_id = :user_id
             ORDER BY o.created_at DESC, o.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $this->attachItems($statement->fetchAll());
    }

    public function allForAdmin(?string $status = null): array
    {
        if ($status !== null && !in_array($status, self::ORDER_STATUSES, true)) {
            throw new InvalidArgumentException('Filter status pesanan tidak valid.');
        }

        $sql =
            'SELECT o.id, o.order_code, o.order_type, o.subtotal, o.discount_amount,
                    o.total_amount, o.status, o.created_at, u.name AS customer_name,
                    u.email AS customer_email, p.payment_method,
                    p.status AS payment_status, p.paid_at, b.booking_date,
                    b.start_time, b.end_time, t.table_number
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             INNER JOIN payments p ON p.order_id = o.id
             LEFT JOIN bookings b ON b.id = o.booking_id
             LEFT JOIN tables t ON t.id = b.table_id';
        if ($status !== null) {
            $sql .= ' WHERE o.status = :status';
        }
        $sql .= ' ORDER BY o.created_at DESC, o.id DESC';

        $statement = $this->db->prepare($sql);
        if ($status !== null) {
            $statement->bindValue('status', $status);
        }
        $statement->execute();

        return $this->attachItems($statement->fetchAll());
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        if (!in_array($status, self::ORDER_STATUSES, true)) {
            throw new InvalidArgumentException('Status pesanan tidak valid.');
        }

        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT user_id, order_code, status FROM orders WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $orderId]);
            $order = $statement->fetch();
            if (!$order) {
                throw new DomainException('Pesanan tidak ditemukan.');
            }
            if ($order['status'] === $status) {
                $this->db->commit();
                return false;
            }

            $update = $this->db->prepare(
                'UPDATE orders SET status = :status WHERE id = :id'
            );
            $update->execute(['status' => $status, 'id' => $orderId]);
            NotificationHelper::notifyUser(
                (int) $order['user_id'],
                'Status pesanan diperbarui',
                'Status pesanan ' . $order['order_code'] . ' berubah menjadi ' . $status . '.',
                '/orders'
            );
            $this->db->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function confirmCashPayment(int $orderId): bool
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT p.status AS payment_status, p.payment_method, o.status AS order_status
                 FROM payments p
                 INNER JOIN orders o ON o.id = p.order_id
                 WHERE p.order_id = :order_id
                 FOR UPDATE'
            );
            $statement->execute(['order_id' => $orderId]);
            $payment = $statement->fetch();
            if (!$payment) {
                throw new DomainException('Pembayaran pesanan tidak ditemukan.');
            }
            if ($payment['payment_method'] !== 'Cash') {
                throw new DomainException('Pesanan ini bukan pembayaran tunai.');
            }
            if ($payment['order_status'] === 'Cancelled') {
                throw new DomainException('Pembayaran pesanan yang dibatalkan tidak dapat dikonfirmasi.');
            }
            if ($payment['payment_status'] === 'Paid') {
                $this->db->commit();
                return false;
            }

            $update = $this->db->prepare(
                "UPDATE payments
                 SET status = 'Paid', paid_at = CURRENT_TIMESTAMP
                 WHERE order_id = :order_id AND status = 'Unpaid'"
            );
            $update->execute(['order_id' => $orderId]);
            $this->db->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function insertOrderWithUniqueCode(
        int $userId,
        ?int $bookingId,
        string $orderType,
        array $amounts
    ): array {
        $statement = $this->db->prepare(
            "INSERT INTO orders
                (order_code, user_id, booking_id, order_type, subtotal, discount_amount, total_amount, status)
             VALUES
                (:order_code, :user_id, :booking_id, :order_type, :subtotal, :discount_amount, :total_amount, 'Pending')"
        );

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            try {
                $statement->execute([
                    'order_code' => $orderCode,
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'order_type' => $orderType,
                    'subtotal' => $amounts['subtotal'],
                    'discount_amount' => $amounts['discount'],
                    'total_amount' => $amounts['total'],
                ]);

                return [
                    'id' => (int) $this->db->lastInsertId(),
                    'order_code' => $orderCode,
                ];
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000' || $attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Kode pesanan unik gagal dibuat.');
    }

    private function attachItems(array $orders): array
    {
        if (!$orders) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($orders as $index => $order) {
            $placeholder = ':order_id_' . $index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = (int) $order['id'];
            $orders[$index]['items'] = [];
        }

        $sql = 'SELECT order_id, menu_name_snapshot, price_snapshot, quantity, notes, subtotal
                FROM order_items
                WHERE order_id IN (' . implode(', ', $placeholders) . ')
                ORDER BY id';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        $itemsByOrder = [];
        foreach ($statement->fetchAll() as $item) {
            $itemsByOrder[(int) $item['order_id']][] = $item;
        }

        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[(int) $order['id']] ?? [];
        }
        unset($order);

        return $orders;
    }

    private function centsToDecimal(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
