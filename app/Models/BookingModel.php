<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class BookingModel extends Model
{
    public function availableTables(string $date, string $startTime, string $endTime, int $guestCount): array
    {
        $this->assertDateIsOpen($date);
        $this->assertWithinOperatingHours($date, $startTime, $endTime);

        $statement = $this->db->prepare(
            "SELECT t.id, t.table_number, t.capacity
             FROM tables t
             WHERE t.is_active = 1
               AND t.capacity >= :guest_count
               AND NOT EXISTS (
                    SELECT 1
                    FROM bookings b
                    WHERE b.table_id = t.id
                      AND b.booking_date = :booking_date
                      AND b.status = 'Confirmed'
                      AND b.start_time < :end_time
                      AND b.end_time > :start_time
               )
             ORDER BY t.capacity, t.table_number"
        );
        $statement->execute([
            'guest_count' => $guestCount,
            'booking_date' => $date,
            'end_time' => $endTime,
            'start_time' => $startTime,
        ]);

        return $statement->fetchAll();
    }

    public function createConfirmed(
        int $userId,
        int $tableId,
        string $date,
        string $startTime,
        string $endTime,
        int $guestCount,
        ?string $notes
    ): int {
        $this->db->beginTransaction();
        try {
            $bookingId = $this->createConfirmedInTransaction(
                $userId,
                $tableId,
                $date,
                $startTime,
                $endTime,
                $guestCount,
                $notes
            );
            $this->db->commit();

            return $bookingId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function createConfirmedInTransaction(
        int $userId,
        int $tableId,
        string $date,
        string $startTime,
        string $endTime,
        int $guestCount,
        ?string $notes
    ): int {
        if (!$this->db->inTransaction()) {
            throw new LogicException('A transaction is required to create this reservation.');
        }

        $tableStatement = $this->db->prepare(
            'SELECT id, table_number, capacity, is_active FROM tables WHERE id = :id FOR UPDATE'
        );
        $tableStatement->execute(['id' => $tableId]);
        $table = $tableStatement->fetch();

        if (!$table || !(int) $table['is_active']) {
            throw new DomainException('Meja tidak tersedia. Pilih meja lain.');
        }
        if ((int) $table['capacity'] < $guestCount) {
            throw new DomainException('Kapasitas meja tidak mencukupi jumlah tamu.');
        }

        $this->assertDateIsOpen($date);
        $this->assertWithinOperatingHours($date, $startTime, $endTime);

        $overlapStatement = $this->db->prepare(
            "SELECT id
             FROM bookings
             WHERE table_id = :table_id
               AND booking_date = :booking_date
               AND status = 'Confirmed'
               AND start_time < :end_time
               AND end_time > :start_time
             LIMIT 1
             FOR UPDATE"
        );
        $overlapStatement->execute([
            'table_id' => $tableId,
            'booking_date' => $date,
            'end_time' => $endTime,
            'start_time' => $startTime,
        ]);
        if ($overlapStatement->fetch()) {
            throw new DomainException('Meja baru saja dipesan pada waktu tersebut. Pilih meja lain atau waktu lain.');
        }

        $insert = $this->db->prepare(
            "INSERT INTO bookings
                (user_id, table_id, booking_date, start_time, end_time, guest_count, notes, status)
             VALUES
                (:user_id, :table_id, :booking_date, :start_time, :end_time, :guest_count, :notes, 'Confirmed')"
        );
        $insert->execute([
            'user_id' => $userId,
            'table_id' => $tableId,
            'booking_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'guest_count' => $guestCount,
            'notes' => $notes,
        ]);

        $bookingId = (int) $this->db->lastInsertId();
        NotificationHelper::notifyAdmins(
            'Reservasi baru',
            'Reservasi meja ' . $table['table_number'] . ' untuk ' . $date
                . ' pukul ' . substr($startTime, 0, 5) . ' berhasil dibuat.',
            '/admin/bookings'
        );
        NotificationHelper::notifyUser(
            $userId,
            'Reservasi berhasil dikonfirmasi',
            'Reservasi Anda pada ' . $date . ' pukul ' . substr($startTime, 0, 5) . ' telah dikonfirmasi.',
            '/bookings'
        );

        return $bookingId;
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.id, b.booking_date, b.start_time, b.end_time, b.guest_count,
                    b.notes, b.status, b.created_at, t.table_number
             FROM bookings b
             INNER JOIN tables t ON t.id = b.table_id
             WHERE b.user_id = :user_id
             ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function allForAdmin(): array
    {
        $statement = $this->db->query(
            'SELECT b.id, b.booking_date, b.start_time, b.end_time, b.guest_count,
                    b.notes, b.status, b.created_at, u.name AS customer_name,
                    u.email AS customer_email, t.table_number
             FROM bookings b
             INNER JOIN users u ON u.id = b.user_id
             INNER JOIN tables t ON t.id = b.table_id
             ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC'
        );

        return $statement->fetchAll();
    }

    public function updateAdminStatus(int $bookingId, string $status): void
    {
        if (!in_array($status, ['Completed', 'Cancelled'], true)) {
            throw new InvalidArgumentException('Status reservasi tidak valid.');
        }

        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT user_id, booking_date, status FROM bookings WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $bookingId]);
            $booking = $statement->fetch();
            if (!$booking) {
                throw new DomainException('Reservasi tidak ditemukan.');
            }

            if ($booking['status'] !== $status) {
                $update = $this->db->prepare(
                    'UPDATE bookings SET status = :status WHERE id = :id'
                );
                $update->execute(['status' => $status, 'id' => $bookingId]);
                if ($status === 'Cancelled') {
                    NotificationHelper::notifyUser(
                        (int) $booking['user_id'],
                        'Reservasi dibatalkan',
                        'Reservasi Anda pada ' . $booking['booking_date'] . ' telah dibatalkan.',
                        '/bookings'
                    );
                }
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function assertDateIsOpen(string $date): void
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM reservation_closures
             WHERE start_date <= :booking_date_start AND end_date >= :booking_date_end
             LIMIT 1'
        );
        $statement->execute([
            'booking_date_start' => $date,
            'booking_date_end' => $date,
        ]);
        if ($statement->fetchColumn()) {
            throw new DomainException('Reservasi ditutup pada tanggal yang dipilih.');
        }
    }

    private function assertWithinOperatingHours(string $date, string $startTime, string $endTime): void
    {
        $statement = $this->db->prepare(
            'SELECT open_time, close_time, is_closed
             FROM operating_hours
             WHERE day_of_week = DAYOFWEEK(:booking_date) - 1
             LIMIT 1'
        );
        $statement->execute(['booking_date' => $date]);
        $hours = $statement->fetch();

        if (!$hours || (int) $hours['is_closed']) {
            throw new DomainException('Cafe tutup pada hari yang dipilih.');
        }
        if ($startTime < $hours['open_time'] || $endTime > $hours['close_time']) {
            throw new DomainException('Waktu reservasi harus berada dalam jam operasional.');
        }
    }
}
