<?php

require_once dirname(__DIR__) . '/Core/Model.php';

class AdminReport extends Model
{
    public function forPeriod(string $startDate, string $endDate): array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
        $startErrors = DateTimeImmutable::getLastErrors();
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);
        $endErrors = DateTimeImmutable::getLastErrors();
        if (
            !$start
            || !$end
            || ($startErrors !== false && ($startErrors['warning_count'] || $startErrors['error_count']))
            || ($endErrors !== false && ($endErrors['warning_count'] || $endErrors['error_count']))
            || $start->format('Y-m-d') !== $startDate
            || $end->format('Y-m-d') !== $endDate
            || $startDate > $endDate
        ) {
            throw new InvalidArgumentException('Rentang tanggal laporan tidak valid.');
        }

        $period = [
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $end->modify('+1 day')->format('Y-m-d') . ' 00:00:00',
        ];
        $summaryStatement = $this->db->prepare(
            "SELECT COUNT(o.id) AS transaction_count,
                    COALESCE(SUM(CASE WHEN p.status = 'Paid' THEN p.amount ELSE 0 END), 0) AS revenue
             FROM orders o
             LEFT JOIN payments p ON p.order_id = o.id
             WHERE o.created_at >= :start_date AND o.created_at < :end_date"
        );
        $summaryStatement->execute($period);
        $summary = $summaryStatement->fetch();

        $bookingStatement = $this->db->prepare(
            'SELECT COUNT(*) FROM bookings
             WHERE booking_date >= :start_date AND booking_date <= :end_date'
        );
        $bookingStatement->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $transactionsStatement = $this->db->prepare(
            'SELECT o.id, o.order_code, o.order_type, o.subtotal, o.discount_amount,
                    o.total_amount, o.status, o.created_at, u.name AS customer_name,
                    p.status AS payment_status, p.payment_method, p.paid_at
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             LEFT JOIN payments p ON p.order_id = o.id
             WHERE o.created_at >= :start_date AND o.created_at < :end_date
             ORDER BY o.created_at DESC, o.id DESC'
        );
        $transactionsStatement->execute($period);

        $topMenusStatement = $this->db->prepare(
            "SELECT oi.menu_name_snapshot, SUM(oi.quantity) AS quantity_sold
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.created_at >= :start_date
               AND o.created_at < :end_date
               AND o.status <> 'Cancelled'
             GROUP BY oi.menu_name_snapshot
             ORDER BY quantity_sold DESC, oi.menu_name_snapshot
             LIMIT 5"
        );
        $topMenusStatement->execute($period);

        return [
            'revenue' => (string) $summary['revenue'],
            'transaction_count' => (int) $summary['transaction_count'],
            'booking_count' => (int) $bookingStatement->fetchColumn(),
            'transactions' => $transactionsStatement->fetchAll(),
            'top_menus' => $topMenusStatement->fetchAll(),
        ];
    }
}
