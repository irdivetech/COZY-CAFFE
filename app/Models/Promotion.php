<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class Promotion extends Model
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT p.id, p.name, p.discount_type, p.discount_value, p.start_date,
                    p.end_date, p.is_active, COUNT(pm.menu_id) AS menu_count
             FROM promotions p
             LEFT JOIN promotion_menus pm ON pm.promotion_id = p.id
             GROUP BY p.id
             ORDER BY p.created_at DESC, p.id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, description, discount_type, discount_value,
                    start_date, end_date, is_active
             FROM promotions WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $promotion = $statement->fetch();
        if (!$promotion) {
            return null;
        }

        $menuStatement = $this->db->prepare(
            'SELECT menu_id FROM promotion_menus WHERE promotion_id = :promotion_id'
        );
        $menuStatement->execute(['promotion_id' => $id]);
        $promotion['menu_ids'] = array_map('intval', $menuStatement->fetchAll(PDO::FETCH_COLUMN));

        return $promotion;
    }

    public function menuOptions(): array
    {
        return $this->db->query(
            'SELECT id, name, is_available FROM menus ORDER BY name'
        )->fetchAll();
    }

    public function save(array $promotion): int
    {
        $this->db->beginTransaction();
        try {
            if (!empty($promotion['id'])) {
                $id = (int) $promotion['id'];
                $statement = $this->db->prepare(
                    'UPDATE promotions
                     SET name = :name, description = :description, discount_type = :discount_type,
                         discount_value = :discount_value, start_date = :start_date,
                         end_date = :end_date, is_active = :is_active
                     WHERE id = :id'
                );
                $statement->execute([
                    'name' => $promotion['name'],
                    'description' => $promotion['description'],
                    'discount_type' => $promotion['discount_type'],
                    'discount_value' => $promotion['discount_value'],
                    'start_date' => $promotion['start_date'],
                    'end_date' => $promotion['end_date'],
                    'is_active' => $promotion['is_active'] ? 1 : 0,
                    'id' => $id,
                ]);
                $deleteMenus = $this->db->prepare(
                    'DELETE FROM promotion_menus WHERE promotion_id = :promotion_id'
                );
                $deleteMenus->execute(['promotion_id' => $id]);
            } else {
                $statement = $this->db->prepare(
                    'INSERT INTO promotions
                        (name, description, discount_type, discount_value, start_date, end_date, is_active)
                     VALUES
                        (:name, :description, :discount_type, :discount_value, :start_date, :end_date, :is_active)'
                );
                $statement->execute([
                    'name' => $promotion['name'],
                    'description' => $promotion['description'],
                    'discount_type' => $promotion['discount_type'],
                    'discount_value' => $promotion['discount_value'],
                    'start_date' => $promotion['start_date'],
                    'end_date' => $promotion['end_date'],
                    'is_active' => $promotion['is_active'] ? 1 : 0,
                ]);
                $id = (int) $this->db->lastInsertId();
            }

            if ($promotion['menu_ids']) {
                $insertMenu = $this->db->prepare(
                    'INSERT INTO promotion_menus (promotion_id, menu_id)
                     VALUES (:promotion_id, :menu_id)'
                );
                foreach ($promotion['menu_ids'] as $menuId) {
                    $insertMenu->execute([
                        'promotion_id' => $id,
                        'menu_id' => $menuId,
                    ]);
                }
            }

            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM promotions WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
