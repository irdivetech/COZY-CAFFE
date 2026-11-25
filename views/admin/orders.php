<?php
$pageTitle = $pageTitle ?? 'Kelola Pesanan';
$orders = $orders ?? [];
$errors = $errors ?? [];
$selectedStatus = $selectedStatus ?? '';
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
$statuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Completed', 'Cancelled'];
$statusClass = static fn (string $status): string => match ($status) {
    'Completed', 'Ready', 'Paid' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    'Preparing' => 'text-bg-warning',
    default => 'text-bg-secondary',
};
$orderStatusLabel = static fn (string $status): string => match ($status) {
    'Pending' => 'Menunggu Konfirmasi',
    'Confirmed' => 'Dikonfirmasi',
    'Preparing' => 'Sedang Disiapkan',
    'Ready' => 'Siap Diambil/Disajikan',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan',
    default => $status,
};
$paymentStatusLabel = static fn (string $status): string => match ($status) {
    'Paid' => 'Lunas',
    'Unpaid' => 'Belum Lunas',
    default => $status,
};
$orderTypeLabel = static fn (string $type): string => match ($type) {
    'Dine-in' => 'Makan di Tempat (Dine-in)',
    'Takeaway' => 'Bawa Pulang (Takeaway)',
    default => $type,
};
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Pesanan Pelanggan</h1>
    <?php if ($errors): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><div><?= $escape($error) ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="get" action="<?= $escape($baseUrl) ?>/admin/orders" class="cozy-card p-3 mb-4 d-flex flex-wrap align-items-end gap-3">
        <div>
            <label class="form-label fw-semibold" for="statusFilter">Filter Berdasarkan Status Pesanan</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">Semua Status Pesanan</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $escape($status) ?>" <?= $selectedStatus === $status ? 'selected' : '' ?>><?= $orderStatusLabel($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-filter me-1"></i>Terapkan Filter</button>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/orders">Reset</a>
    </form>

    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Kode Pesanan</th><th>Pelanggan</th><th>Item Menu</th><th>Tipe / Reservasi</th><th>Total</th><th>Pembayaran</th><th>Status & Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-coffee"><?= $escape($order['order_code']) ?></div>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= $escape($order['created_at']) ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= $escape($order['customer_name']) ?></div>
                            <small class="text-muted"><?= $escape($order['customer_email']) ?></small>
                        </td>
                        <td>
                            <ul class="list-unstyled mb-0 small">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li><?= $escape($item['menu_name_snapshot']) ?> × <?= (int) $item['quantity'] ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $orderTypeLabel($order['order_type']) ?></span>
                            <?php if ($order['order_type'] === 'Dine-in' && $order['booking_date']): ?>
                                <div class="small text-muted mt-1">Meja <?= $escape($order['table_number']) ?> · <?= $escape($order['booking_date']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap fw-semibold">Rp <?= $money($order['total_amount']) ?></td>
                        <td>
                            <span class="badge <?= $statusClass($order['payment_status']) ?>"><?= $paymentStatusLabel($order['payment_status']) ?></span>
                            <div class="small text-muted mt-1"><?= $escape($order['payment_method']) ?></div>
                            <?php if ($order['payment_status'] === 'Unpaid' && $order['status'] !== 'Cancelled'): ?>
                                <form method="post" action="<?= $escape($baseUrl) ?>/admin/orders/payment" class="mt-2" onsubmit="return confirm('Konfirmasi bahwa pembayaran tunai pesanan ini sudah diterima kasir?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                    <button class="btn btn-sm btn-outline-success text-nowrap" type="submit"><i class="fa-solid fa-check me-1"></i>Konfirmasi Lunas</button>
                                </form>
                            <?php elseif ($order['paid_at']): ?>
                                <div class="small text-success mt-1"><i class="fa-solid fa-circle-check me-1"></i><?= $escape($order['paid_at']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="mb-2">
                                <span class="badge <?= $statusClass($order['status']) ?>"><?= $orderStatusLabel($order['status']) ?></span>
                            </div>
                            <form method="post" action="<?= $escape($baseUrl) ?>/admin/orders/status" class="d-flex gap-1 align-items-center">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select class="form-select form-select-sm" name="status" style="width: auto;">
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $escape($st) ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $orderStatusLabel($st) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-coffee text-nowrap" type="submit">Ubah</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data pesanan yang sesuai filter.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
