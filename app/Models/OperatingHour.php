<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class OperatingHour extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT day_of_week, open_time, close_time, is_closed
             FROM operating_hours ORDER BY day_of_week'
        )->fetchAll();
    }

    public function saveAll(array $hours): void
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'INSERT INTO operating_hours (day_of_week, open_time, close_time, is_closed)
                 VALUES (:day_of_week, :open_time, :close_time, :is_closed)
                 ON DUPLICATE KEY UPDATE
                    open_time = VALUES(open_time),
                    close_time = VALUES(close_time),
                    is_closed = VALUES(is_closed)'
            );

            foreach ($hours as $hour) {
                $statement->execute([
                    'day_of_week' => $hour['day_of_week'],
                    'open_time' => $hour['open_time'],
                    'close_time' => $hour['close_time'],
                    'is_closed' => $hour['is_closed'] ? 1 : 0,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
