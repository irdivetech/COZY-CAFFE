<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class Category extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT id, name, description, created_at FROM categories ORDER BY name')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, description FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category ?: null;
    }

    public function create(string $name, ?string $description): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO categories (name, description) VALUES (:name, :description)'
        );
        $statement->execute(['name' => $name, 'description' => $description]);
    }

    public function update(int $id, string $name, ?string $description): void
    {
        $statement = $this->db->prepare(
            'UPDATE categories SET name = :name, description = :description WHERE id = :id'
        );
        $statement->execute([
            'name' => $name,
            'description' => $description,
            'id' => $id,
        ]);
    }

    public function menuCount(int $id): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM menus WHERE category_id = :id');
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE name = :name';
        $parameters = ['name' => $name];

        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->db->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
