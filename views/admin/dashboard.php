<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$summary = $summary ?? [];
$recentOrders = $recentOrders ?? [];
$recentBookings = $recentBookings ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
$statusClass = static fn (string $status): string => match ($status) {
    'Completed', 'Ready', 'Paid' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    'Preparing' => 'text-bg-warning',
    default => 'text-bg-secondary',
};
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">CozyCafe overview</p>
            <h1 class="section-title h2 mb-0">Dashboard</h1>
        </div>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/reports">View Reports</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><article class="cozy-card p-4 h-100"><div class="small text-muted">Total Orders</div><div class="display-6 fw-semibold"><?= (int) ($summary['total_orders'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3"><article class="cozy-card p-4 h-100"><div class="small text-muted">Total Reservations</div><div class="display-6 fw-semibold"><?= (int) ($summary['total_bookings'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3"><article class="cozy-card p-4 h-100"><div class="small text-muted">Total Customers</div><div class="display-6 fw-semibold"><?= (int) ($summary['total_customers'] ?? 0) ?></div></article></div>
        <div class="col-sm-6 col-xl-3"><article class="cozy-card p-4 h-100"><div class="small text-muted">Paid Revenue</div><div class="h3 fw-semibold mb-0">Rp <?= $money($summary['paid_revenue'] ?? 0) ?></div></article></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="cozy-card">
                <div class="cozy-card-header p-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">5 Latest Orders</h2>
                    <a class="small" href="<?= $escape($baseUrl) ?>/admin/orders">Manage orders</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><span class="fw-semibold"><?= $escape($order['order_code']) ?></span><br><small class="text-muted"><?= $escape($order['created_at']) ?></small></td>
                                <td><?= $escape($order['customer_name']) ?></td>
                                <td>Rp <?= $money($order['total_amount']) ?></td>
                                <td><span class="badge <?= $statusClass($order['status']) ?>"><?= $escape($order['status']) ?></span><br><small class="text-muted"><?= $escape($order['payment_status']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentOrders): ?><tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="cozy-card">
                <div class="cozy-card-header p-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">5 Latest Reservations</h2>
                    <a class="small" href="<?= $escape($baseUrl) ?>/admin/bookings">Manage bookings</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Customer / Date</th><th>Table</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><?= $escape($booking['customer_name']) ?><br><small class="text-muted"><?= $escape($booking['booking_date']) ?> · <?= $escape(substr($booking['start_time'], 0, 5)) ?></small></td>
                                <td><?= $escape($booking['table_number']) ?><br><small class="text-muted"><?= (int) $booking['guest_count'] ?> guests</small></td>
                                <td><span class="badge <?= $statusClass($booking['status']) ?>"><?= $escape($booking['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentBookings): ?><tr><td colspan="3" class="text-center text-muted py-4">No reservations yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
