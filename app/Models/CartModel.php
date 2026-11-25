<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class CartModel extends Model
{
    public function itemCount(int $userId): int
    {
        $statement = $this->db->prepare(
            'SELECT COALESCE(SUM(ci.quantity), 0)
             FROM carts c
             LEFT JOIN cart_items ci ON ci.cart_id = c.id
             WHERE c.user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function getCart(int $userId): array
    {
        return $this->loadCart($userId, false);
    }

    public function getCartForCheckout(int $userId): array
    {
        return $this->loadCart($userId, true);
    }

    private function loadCart(int $userId, bool $lockRows): array
    {
        $sql =
            "SELECT ci.id AS cart_item_id, ci.menu_id, ci.quantity, ci.notes,
                    m.name AS menu_name, m.description, m.price, m.image, m.is_available,
                    p.id AS promotion_id, p.name AS promotion_name,
                    p.discount_type, p.discount_value
             FROM carts c
             INNER JOIN cart_items ci ON ci.cart_id = c.id
             INNER JOIN menus m ON m.id = ci.menu_id
             LEFT JOIN promotion_menus pm ON pm.menu_id = m.id
             LEFT JOIN promotions p ON p.id = pm.promotion_id
                 AND p.is_active = 1
                 AND CURRENT_DATE BETWEEN p.start_date AND p.end_date
             WHERE c.user_id = :user_id
             ORDER BY ci.id, p.id";
        if ($lockRows) {
            $sql .= ' FOR UPDATE';
        }
        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            $itemId = (int) $row['cart_item_id'];
            if (!isset($items[$itemId])) {
                $priceCents = $this->decimalToCents($row['price']);
                $items[$itemId] = [
                    'cart_item_id' => $itemId,
                    'menu_id' => (int) $row['menu_id'],
                    'menu_name' => $row['menu_name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'image' => $row['image'],
                    'is_available' => (bool) $row['is_available'],
                    'quantity' => (int) $row['quantity'],
                    'notes' => $row['notes'],
                    'promotion_name' => null,
                    'unit_discount_cents' => 0,
                    'unit_price_cents' => $priceCents,
                ];
            }

            if ($row['promotion_id'] !== null) {
                $discountCents = $this->promotionDiscountCents(
                    $items[$itemId]['unit_price_cents'],
                    $row['discount_type'],
                    $row['discount_value']
                );
                if ($discountCents > $items[$itemId]['unit_discount_cents']) {
                    $items[$itemId]['unit_discount_cents'] = $discountCents;
                    $items[$itemId]['promotion_name'] = $row['promotion_name'];
                }
            }
        }

        $subtotalCents = 0;
        $discountCents = 0;
        foreach ($items as &$item) {
            $item['line_subtotal_cents'] = $item['unit_price_cents'] * $item['quantity'];
            $item['line_discount_cents'] = $item['unit_discount_cents'] * $item['quantity'];
            $item['line_total_cents'] = max(
                0,
                $item['line_subtotal_cents'] - $item['line_discount_cents']
            );
            $subtotalCents += $item['line_subtotal_cents'];
            $discountCents += $item['line_discount_cents'];
        }
        unset($item);

        return [
            'items' => array_values($items),
            'item_count' => array_sum(array_column($items, 'quantity')),
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'total_cents' => max(0, $subtotalCents - $discountCents),
            'has_unavailable_items' => (bool) array_filter(
                $items,
                static fn (array $item): bool => !$item['is_available']
            ),
        ];
    }

    public function addItem(int $userId, int $menuId, int $quantity, ?string $notes): void
    {
        $this->db->beginTransaction();
        try {
            $cartId = $this->getOrCreateCartId($userId);
            $menuStatement = $this->db->prepare(
                'SELECT id, is_available FROM menus WHERE id = :id FOR UPDATE'
            );
            $menuStatement->execute(['id' => $menuId]);
            $menu = $menuStatement->fetch();
            if (!$menu || !(int) $menu['is_available']) {
                throw new DomainException('Menu tidak tersedia dan tidak dapat ditambahkan ke keranjang.');
            }

            $statement = $this->db->prepare(
                'INSERT INTO cart_items (cart_id, menu_id, quantity, notes)
                 VALUES (:cart_id, :menu_id, :quantity, :notes)
                 ON DUPLICATE KEY UPDATE
                    quantity = quantity + :increment,
                    notes = :updated_notes'
            );
            $statement->execute([
                'cart_id' => $cartId,
                'menu_id' => $menuId,
                'quantity' => $quantity,
                'notes' => $notes,
                'increment' => $quantity,
                'updated_notes' => $notes,
            ]);

            $quantityStatement = $this->db->prepare(
                'SELECT quantity FROM cart_items WHERE cart_id = :cart_id AND menu_id = :menu_id FOR UPDATE'
            );
            $quantityStatement->execute(['cart_id' => $cartId, 'menu_id' => $menuId]);
            if ((int) $quantityStatement->fetchColumn() > 99) {
                throw new DomainException('Jumlah maksimal untuk satu menu adalah 99.');
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function updateItem(int $userId, int $cartItemId, int $quantity, ?string $notes): void
    {
        $statement = $this->db->prepare(
            'UPDATE cart_items ci
             INNER JOIN carts c ON c.id = ci.cart_id
             SET ci.quantity = :quantity, ci.notes = :notes
             WHERE ci.id = :item_id AND c.user_id = :user_id'
        );
        $statement->execute([
            'quantity' => $quantity,
            'notes' => $notes,
            'item_id' => $cartItemId,
            'user_id' => $userId,
        ]);

        if ($statement->rowCount() === 0 && !$this->itemBelongsToUser($userId, $cartItemId)) {
            throw new DomainException('Item keranjang tidak ditemukan.');
        }
    }

    public function changeQuantity(int $userId, int $cartItemId, int $delta): void
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT ci.quantity
                 FROM cart_items ci
                 INNER JOIN carts c ON c.id = ci.cart_id
                 WHERE ci.id = :item_id AND c.user_id = :user_id
                 FOR UPDATE'
            );
            $statement->execute([
                'item_id' => $cartItemId,
                'user_id' => $userId,
            ]);
            $quantity = $statement->fetchColumn();
            if ($quantity === false) {
                throw new DomainException('Item keranjang tidak ditemukan.');
            }

            $newQuantity = (int) $quantity + $delta;
            if ($newQuantity < 1) {
                $this->deleteItem($userId, $cartItemId);
            } elseif ($newQuantity > 99) {
                throw new DomainException('Jumlah maksimal untuk satu menu adalah 99.');
            } else {
                $update = $this->db->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
                $update->execute(['quantity' => $newQuantity, 'id' => $cartItemId]);
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteItem(int $userId, int $cartItemId): void
    {
        $statement = $this->db->prepare(
            'DELETE ci FROM cart_items ci
             INNER JOIN carts c ON c.id = ci.cart_id
             WHERE ci.id = :item_id AND c.user_id = :user_id'
        );
        $statement->execute([
            'item_id' => $cartItemId,
            'user_id' => $userId,
        ]);
        if ($statement->rowCount() === 0) {
            throw new DomainException('Item keranjang tidak ditemukan.');
        }
    }

    private function getOrCreateCartId(int $userId): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO carts (user_id) VALUES (:user_id)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $this->db->lastInsertId();
    }

    private function itemBelongsToUser(int $userId, int $cartItemId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1
             FROM cart_items ci
             INNER JOIN carts c ON c.id = ci.cart_id
             WHERE ci.id = :item_id AND c.user_id = :user_id'
        );
        $statement->execute([
            'item_id' => $cartItemId,
            'user_id' => $userId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    private function promotionDiscountCents(int $priceCents, ?string $type, $value): int
    {
        $discountValueCents = $this->decimalToCents($value ?? '0');
        if ($type === 'percentage') {
            $discountValueCents = min($discountValueCents, 10000);
            return min(
                $priceCents,
                intdiv(($priceCents * $discountValueCents) + 5000, 10000)
            );
        }
        if ($type === 'fixed') {
            return min($priceCents, $discountValueCents);
        }

        return 0;
    }

    private function decimalToCents($value): int
    {
        $parts = explode('.', (string) $value, 2);
        $whole = (int) ($parts[0] ?? 0);
        $fraction = str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');

        return max(0, ($whole * 100) + (int) $fraction);
    }
}
