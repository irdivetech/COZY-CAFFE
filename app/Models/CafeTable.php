<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class CafeTable extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, table_number, capacity, is_active, created_at FROM tables ORDER BY table_number'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, table_number, capacity, is_active FROM tables WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $table = $statement->fetch();

        return $table ?: null;
    }

    public function create(string $tableNumber, int $capacity, bool $isActive): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO tables (table_number, capacity, is_active)
             VALUES (:table_number, :capacity, :is_active)'
        );
        $statement->execute([
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'is_active' => $isActive ? 1 : 0,
        ]);
    }

    public function update(int $id, string $tableNumber, int $capacity, bool $isActive): void
    {
        $statement = $this->db->prepare(
            'UPDATE tables SET table_number = :table_number, capacity = :capacity,
                               is_active = :is_active WHERE id = :id'
        );
        $statement->execute([
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'is_active' => $isActive ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function numberExists(string $tableNumber, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM tables WHERE table_number = :table_number';
        $parameters = ['table_number' => $tableNumber];

        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->db->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function toggle(int $id): void
    {
        $statement = $this->db->prepare(
            'UPDATE tables SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}
