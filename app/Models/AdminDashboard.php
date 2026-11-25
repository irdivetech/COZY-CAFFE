<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class AdminDashboard extends Model
{
    public function summary(): array
    {
        $orders = $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $bookings = $this->db->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
        $customers = $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'customer'"
        )->fetchColumn();
        $revenue = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0)
             FROM payments
             WHERE status = 'Paid'"
        )->fetchColumn();

        return [
            'total_orders' => (int) $orders,
            'total_bookings' => (int) $bookings,
            'total_customers' => (int) $customers,
            'paid_revenue' => (string) $revenue,
        ];
    }

    public function recentOrders(): array
    {
        $statement = $this->db->query(
            'SELECT o.id, o.order_code, o.order_type, o.total_amount, o.status,
                    o.created_at, u.name AS customer_name, p.status AS payment_status
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             INNER JOIN payments p ON p.order_id = o.id
             ORDER BY o.created_at DESC, o.id DESC
             LIMIT 5'
        );

        return $statement->fetchAll();
    }

    public function recentBookings(): array
    {
        $statement = $this->db->query(
            'SELECT b.id, b.booking_date, b.start_time, b.end_time, b.guest_count,
                    b.status, u.name AS customer_name, t.table_number
             FROM bookings b
             INNER JOIN users u ON u.id = b.user_id
             INNER JOIN tables t ON t.id = b.table_id
             ORDER BY b.created_at DESC, b.id DESC
             LIMIT 5'
        );

        return $statement->fetchAll();
    }
}
