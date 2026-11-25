<?php
$pageTitle = $pageTitle ?? 'Laporan Penjualan & Reservasi';
$report = $report ?? [];
$startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $endDate ?? date('Y-m-d');
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
$paymentStatusLabel = static fn (string $status): string => match ($status) {
    'Paid' => 'Lunas',
    'Unpaid' => 'Belum Lunas',
    default => $status,
};
$orderTypeLabel = static fn (string $type): string => match ($type) {
    'Dine-in' => 'Dine-in',
    'Takeaway' => 'Takeaway',
    default => $type,
};
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 print-hide">
        <div>
            <h1 class="section-title h2 mb-1">Laporan Penjualan & Operasional</h1>
            <p class="text-muted small mb-0">Analisis pendapatan, ringkasan transaksi, reservasi, dan menu terlaris.</p>
        </div>
        <button class="btn btn-coffee" type="button" onclick="window.print();"><i class="fa-solid fa-print me-2"></i>Cetak / Simpan PDF</button>
    </div>

    <form method="get" action="<?= $escape($baseUrl) ?>/admin/reports" class="cozy-card p-3 mb-4 d-flex flex-wrap align-items-end gap-3 print-hide">
        <div>
            <label class="form-label fw-semibold" for="start_date">Tanggal Mulai</label>
            <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($startDate) ?>" required>
        </div>
        <div>
            <label class="form-label fw-semibold" for="end_date">Tanggal Selesai</label>
            <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($endDate) ?>" required>
        </div>
        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-filter me-1"></i>Terapkan Filter</button>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/reports">Bulan Ini</a>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <article class="cozy-card p-4 h-100">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Pendapatan Lunas (Paid)</div>
                <div class="h3 fw-bold text-coffee mb-0">Rp <?= $money($report['paid_revenue'] ?? 0) ?></div>
            </article>
        </div>
        <div class="col-sm-4">
            <article class="cozy-card p-4 h-100">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Total Pesanan Masuk</div>
                <div class="display-6 fw-bold"><?= (int) ($report['total_orders'] ?? 0) ?></div>
            </article>
        </div>
        <div class="col-sm-4">
            <article class="cozy-card p-4 h-100">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Total Reservasi Meja</div>
                <div class="display-6 fw-bold"><?= (int) ($report['total_bookings'] ?? 0) ?></div>
            </article>
        </div>
    </div>

    <section class="cozy-card mb-4">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0"><i class="fa-solid fa-trophy me-2 text-warning"></i>Top 5 Menu Terlaris (Kuantitas Terjual)</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nama Menu</th><th>Kategori</th><th class="text-center">Jumlah Terjual (Qty)</th><th class="text-end">Subtotal Penjualan</th></tr></thead>
                <tbody>
                <?php foreach (($report['top_menus'] ?? []) as $menu): ?>
                    <tr>
                        <td class="fw-semibold"><?= $escape($menu['menu_name']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $escape($menu['category_name']) ?></span></td>
                        <td class="text-center fw-bold"><?= (int) $menu['total_quantity_sold'] ?> porsi</td>
                        <td class="text-end fw-semibold text-coffee">Rp <?= $money($menu['total_sales_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($report['top_menus'])): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada menu yang terjual pada periode ini.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Daftar Transaksi pada Periode Terpilih</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-transactions">
                <thead class="table-light"><tr><th>Waktu</th><th>Kode Pesanan</th><th>Pelanggan</th><th>Tipe</th><th>Total Pembayaran</th><th>Pembayaran</th><th>Status Pesanan</th></tr></thead>
                <tbody>
                <?php foreach (($report['transactions'] ?? []) as $transaction): ?>
                    <tr>
                        <td class="small text-muted"><?= $escape($transaction['created_at']) ?></td>
                        <td class="fw-bold text-coffee"><?= $escape($transaction['order_code']) ?></td>
                        <td><?= $escape($transaction['customer_name']) ?></td>
                        <td><?= $orderTypeLabel($transaction['order_type']) ?></td>
                        <td class="text-nowrap fw-semibold">Rp <?= $money($transaction['total_amount']) ?></td>
                        <td><span class="badge <?= $statusClass($transaction['payment_status'] ?? 'Unpaid') ?>"><?= $paymentStatusLabel($transaction['payment_status'] ?? 'Unpaid') ?></span></td>
                        <td><span class="badge <?= $statusClass($transaction['status']) ?>"><?= $orderStatusLabel($transaction['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($report['transactions'])): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pesanan pada periode ini.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
