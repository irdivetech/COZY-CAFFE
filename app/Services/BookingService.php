<?php

class BookingService
{
    private BookingModel $bookings;

    public function __construct()
    {
        $this->bookings = new BookingModel();
    }

    public function availableTables(string $date, string $startTime, int $guestCount): array
    {
        $reservation = $this->normalize($date, $startTime, $guestCount);

        return $this->bookings->availableTables(
            $reservation['date'],
            $reservation['start_time'],
            $reservation['end_time'],
            $reservation['guest_count']
        );
    }

    public function create(
        int $userId,
        string $date,
        string $startTime,
        int $guestCount,
        int $tableId,
        ?string $notes
    ): int {
        if ($tableId < 1) {
            throw new InvalidArgumentException('Pilih meja yang tersedia.');
        }

        $reservation = $this->normalize($date, $startTime, $guestCount);

        return $this->bookings->createConfirmed(
            $userId,
            $tableId,
            $reservation['date'],
            $reservation['start_time'],
            $reservation['end_time'],
            $reservation['guest_count'],
            $notes
        );
    }

    public function createWithinTransaction(
        int $userId,
        string $date,
        string $startTime,
        int $guestCount,
        int $tableId,
        ?string $notes
    ): int {
        if ($tableId < 1) {
            throw new InvalidArgumentException('Pilih meja yang tersedia.');
        }

        $reservation = $this->normalize($date, $startTime, $guestCount);

        return $this->bookings->createConfirmedInTransaction(
            $userId,
            $tableId,
            $reservation['date'],
            $reservation['start_time'],
            $reservation['end_time'],
            $reservation['guest_count'],
            $notes
        );
    }

    private function normalize(string $date, string $startTime, int $guestCount): array
    {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (
            !$parsedDate
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $parsedDate->format('Y-m-d') !== $date
        ) {
            throw new InvalidArgumentException('Tanggal reservasi tidak valid.');
        }
        if ($parsedDate->format('Y-m-d') < date('Y-m-d')) {
            throw new InvalidArgumentException('Tanggal reservasi tidak boleh di masa lalu.');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime)) {
            throw new InvalidArgumentException('Jam reservasi tidak valid.');
        }
        if ($guestCount < 1 || $guestCount > 1000) {
            throw new InvalidArgumentException('Jumlah tamu harus antara 1 dan 1000.');
        }

        $start = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $startTime);
        if (!$start || ($date === date('Y-m-d') && $start <= new DateTimeImmutable())) {
            throw new InvalidArgumentException('Jam reservasi tidak boleh berada di masa lalu.');
        }
        $end = $start->modify('+2 hours');
        if ($end->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('Reservasi dua jam harus berakhir pada hari yang sama.');
        }

        return [
            'date' => $date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'guest_count' => $guestCount,
        ];
    }
}
