<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class Menu extends Model
{
    public function allForAdmin(): array
    {
        $sql = $this->catalogSelect() . ' ORDER BY m.name';

        return $this->db->query($sql)->fetchAll();
    }

    public function available(int $limit = 0, ?int $categoryId = null, string $search = ''): array
    {
        $sql = $this->catalogSelect() . ' WHERE m.is_available = 1';
        $parameters = [];

        if ($categoryId !== null) {
            $sql .= ' AND m.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        if ($search !== '') {
            $sql .= ' AND (m.name LIKE :search_name OR m.description LIKE :search_description)';
            $parameters['search_name'] = '%' . $search . '%';
            $parameters['search_description'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY m.name';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function featured(int $limit = 6): array
    {
        $sql = $this->catalogSelect()
            . ' WHERE m.is_available = 1 ORDER BY (p.id IS NOT NULL) DESC, m.created_at DESC LIMIT '
            . (int) $limit;

        return $this->db->query($sql)->fetchAll();
    }

    public function findAvailable(int $id): ?array
    {
        $statement = $this->db->prepare(
            $this->catalogSelect() . ' WHERE m.is_available = 1 AND m.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $menu = $statement->fetch();

        return $menu ?: null;
    }

    public function findForAdmin(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, category_id, name, description, price, image, is_available
             FROM menus WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $menu = $statement->fetch();

        return $menu ?: null;
    }

    public function create(
        int $categoryId,
        string $name,
        ?string $description,
        string $price,
        ?string $image,
        bool $isAvailable
    ): int {
        $statement = $this->db->prepare(
            'INSERT INTO menus (category_id, name, description, price, image, is_available)
             VALUES (:category_id, :name, :description, :price, :image, :is_available)'
        );
        $statement->execute([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image' => $image,
            'is_available' => $isAvailable ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        int $categoryId,
        string $name,
        ?string $description,
        string $price,
        ?string $image,
        bool $replaceImage,
        bool $isAvailable
    ): void {
        $sql = 'UPDATE menus
                SET category_id = :category_id, name = :name, description = :description,
                    price = :price, is_available = :is_available';
        $parameters = [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'is_available' => $isAvailable ? 1 : 0,
            'id' => $id,
        ];

        if ($replaceImage) {
            $sql .= ', image = :image';
            $parameters['image'] = $image;
        }

        $statement = $this->db->prepare($sql . ' WHERE id = :id');
        $statement->execute($parameters);
    }

    public function disable(int $id): void
    {
        $statement = $this->db->prepare('UPDATE menus SET is_available = 0 WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function catalogSelect(): string
    {
        return "SELECT m.id, m.category_id, m.name, m.description, m.price, m.image,
                       m.is_available, c.name AS category_name,
                       p.name AS promotion_name, p.discount_type, p.discount_value
                FROM menus m
                INNER JOIN categories c ON c.id = m.category_id
                LEFT JOIN promotions p ON p.id = (
                    SELECT active_promotion.id
                    FROM promotion_menus pm
                    INNER JOIN promotions active_promotion ON active_promotion.id = pm.promotion_id
                    WHERE pm.menu_id = m.id
                      AND active_promotion.is_active = 1
                      AND CURRENT_DATE BETWEEN active_promotion.start_date AND active_promotion.end_date
                    ORDER BY active_promotion.id DESC
                    LIMIT 1
                )";
    }
}
