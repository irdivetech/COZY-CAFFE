<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class AdminCustomer extends Model
{
    public function all(): array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.phone, u.created_at,
                    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                    (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS booking_count
             FROM users u
             WHERE u.role = :role
             ORDER BY u.created_at DESC'
        );
        $statement->execute(['role' => 'customer']);

        return $statement->fetchAll();
    }
}
