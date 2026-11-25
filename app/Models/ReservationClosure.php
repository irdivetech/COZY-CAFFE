<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class ReservationClosure extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, start_date, end_date, reason, created_at
             FROM reservation_closures ORDER BY start_date DESC, id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, start_date, end_date, reason
             FROM reservation_closures WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $closure = $statement->fetch();

        return $closure ?: null;
    }

    public function create(string $startDate, string $endDate, string $reason): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO reservation_closures (start_date, end_date, reason)
             VALUES (:start_date, :end_date, :reason)'
        );
        $statement->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
        ]);
    }

    public function update(int $id, string $startDate, string $endDate, string $reason): void
    {
        $statement = $this->db->prepare(
            'UPDATE reservation_closures
             SET start_date = :start_date, end_date = :end_date, reason = :reason
             WHERE id = :id'
        );
        $statement->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM reservation_closures WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
