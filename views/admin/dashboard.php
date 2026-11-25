<?php
$pageTitle = $pageTitle ?? 'Dashboard Admin';
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
$orderStatusLabel = static fn (string $status): string => match ($status) {
    'Pending' => 'Menunggu',
    'Confirmed' => 'Dikonfirmasi',
    'Preparing' => 'Disiapkan',
    'Ready' => 'Siap',
    'Completed' => 'Selesai',
    'Cancelled' => 'Batal',
    default => $status,
};
$bookingStatusLabel = static fn (string $status): string => match ($status) {
    'Confirmed' => 'Dikonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Batal',
    default => $status,
};
$paymentStatusLabel = static fn (string $status): string => match ($status) {
    'Paid' => 'Lunas',
    'Unpaid' => 'Belum Lunas',
    default => $status,
};
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Ringkasan Operasional Cafe</p>
            <h1 class="section-title h2 mb-0">Dashboard</h1>
        </div>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/reports"><i class="fa-solid fa-chart-line me-1"></i>Lihat Laporan Penjualan</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <article class="cozy-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted text-uppercase fw-semibold">Total Pesanan</span>
                    <i class="fa-solid fa-receipt text-coffee fs-4"></i>
                </div>
                <div class="display-6 fw-bold"><?= (int) ($summary['total_orders'] ?? 0) ?></div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="cozy-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted text-uppercase fw-semibold">Total Reservasi</span>
                    <i class="fa-solid fa-calendar-check text-coffee fs-4"></i>
                </div>
                <div class="display-6 fw-bold"><?= (int) ($summary['total_bookings'] ?? 0) ?></div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="cozy-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted text-uppercase fw-semibold">Total Pelanggan</span>
                    <i class="fa-solid fa-users text-coffee fs-4"></i>
                </div>
                <div class="display-6 fw-bold"><?= (int) ($summary['total_customers'] ?? 0) ?></div>
            </article>
        </div>
        <div class="col-sm-6 col-xl-3">
            <article class="cozy-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted text-uppercase fw-semibold">Pendapatan Lunas</span>
                    <i class="fa-solid fa-sack-dollar text-success fs-4"></i>
                </div>
                <div class="h3 fw-bold text-coffee mb-0">Rp <?= $money($summary['paid_revenue'] ?? 0) ?></div>
            </article>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="cozy-card">
                <div class="cozy-card-header p-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">5 Pesanan Terbaru</h2>
                    <a class="small text-coffee fw-semibold text-decoration-none" href="<?= $escape($baseUrl) ?>/admin/orders">Kelola pesanan <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Kode Pesanan</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><span class="fw-semibold text-coffee"><?= $escape($order['order_code']) ?></span><br><small class="text-muted"><?= $escape($order['created_at']) ?></small></td>
                                <td><?= $escape($order['customer_name']) ?></td>
                                <td class="fw-semibold">Rp <?= $money($order['total_amount']) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass($order['status']) ?>"><?= $orderStatusLabel($order['status']) ?></span>
                                    <br><small class="text-muted"><?= $paymentStatusLabel($order['payment_status']) ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentOrders): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada pesanan masuk.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="cozy-card">
                <div class="cozy-card-header p-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">5 Reservasi Terbaru</h2>
                    <a class="small text-coffee fw-semibold text-decoration-none" href="<?= $escape($baseUrl) ?>/admin/bookings">Kelola reservasi <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Pelanggan / Tanggal</th><th>Meja</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= $escape($booking['customer_name']) ?></div>
                                    <small class="text-muted"><?= $escape($booking['booking_date']) ?> · <?= $escape(substr($booking['start_time'], 0, 5)) ?>-<?= $escape(substr($booking['end_time'], 0, 5)) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border">Meja <?= $escape($booking['table_number']) ?></span><br><small class="text-muted"><?= (int) $booking['guest_count'] ?> tamu</small></td>
                                <td><span class="badge <?= $statusClass($booking['status']) ?>"><?= $bookingStatusLabel($booking['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentBookings): ?><tr><td colspan="3" class="text-center text-muted py-4">Belum ada reservasi meja.</td></tr><?php endif; ?>
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
