<?php
$pageTitle = $pageTitle ?? 'Pesanan Saya';
$orders = $orders ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
$statusBadge = static fn (string $status): string => match ($status) {
    'Completed' => 'text-bg-success',
    'Ready' => 'text-bg-info text-white',
    'Preparing' => 'text-bg-warning',
    'Confirmed' => 'text-bg-primary',
    'Cancelled' => 'text-bg-danger',
    default => 'text-bg-secondary',
};
$statusLabel = static fn (string $status): string => match ($status) {
    'Pending' => 'Menunggu Konfirmasi',
    'Confirmed' => 'Dikonfirmasi',
    'Preparing' => 'Sedang Disiapkan',
    'Ready' => 'Siap Diambil / Disajikan',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan',
    default => $status,
};
$typeLabel = static fn (string $type): string => match ($type) {
    'Dine-in' => 'Makan di Tempat (Dine-in)',
    'Takeaway' => 'Bawa Pulang (Takeaway)',
    default => $type,
};
$paymentLabel = static fn (string $status): string => match ($status) {
    'Paid' => 'Lunas',
    'Unpaid' => 'Belum Lunas',
    default => $status,
};
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Aktivitas Anda</p>
            <h1 class="section-title h2 mb-0">Riwayat Pesanan</h1>
        </div>
        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/menu"><i class="fa-solid fa-utensils me-1"></i>Pesan Lagi</a>
    </div>

    <?php if (!$orders): ?>
        <div class="cozy-card p-5 text-center">
            <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>
            <h2 class="h5">Belum Ada Riwayat Pesanan</h2>
            <p class="text-muted">Pesanan yang Anda buat akan tercatat dan dapat dilacak statusnya di sini.</p>
            <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/menu">Lihat Menu</a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($orders as $order): ?>
                <article class="cozy-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border-bottom pb-3 mb-3">
                        <div>
                            <div class="fw-bold fs-5 text-coffee"><?= $escape($order['order_code']) ?></div>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= $escape($order['created_at']) ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= $statusBadge($order['status']) ?>"><?= $statusLabel($order['status']) ?></span>
                            <div>
                                <span class="badge <?= $order['payment_status'] === 'Paid' ? 'text-bg-success' : 'text-bg-secondary' ?> mt-1">
                                    <?= $paymentLabel($order['payment_status']) ?> (<?= $escape($order['payment_method']) ?>)
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <h3 class="h6 text-muted mb-2">Item Pesanan:</h3>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="py-1 d-flex justify-content-between">
                                        <div>
                                            <strong><?= $escape($item['menu_name_snapshot']) ?></strong> × <?= (int) $item['quantity'] ?>
                                            <?php if ($item['notes']): ?>
                                                <div class="small text-muted fst-italic">"<?= $escape($item['notes']) ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-nowrap ms-2">Rp <?= $money($item['subtotal']) ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-md-5 border-start-md">
                            <div class="bg-light p-3 rounded">
                                <div class="small mb-1"><strong>Tipe:</strong> <?= $typeLabel($order['order_type']) ?></div>
                                <?php if ($order['order_type'] === 'Dine-in' && $order['table_number']): ?>
                                    <div class="small mb-2"><strong>Reservasi:</strong> Meja <?= $escape($order['table_number']) ?> (<?= $escape($order['booking_date']) ?>)</div>
                                <?php endif; ?>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Subtotal</span>
                                    <span>Rp <?= $money($order['subtotal']) ?></span>
                                </div>
                                <?php if ((float) $order['discount_amount'] > 0): ?>
                                    <div class="d-flex justify-content-between small text-success">
                                        <span>Diskon</span>
                                        <span>−Rp <?= $money($order['discount_amount']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between fw-bold text-coffee mt-1 pt-1 border-top">
                                    <span>Total</span>
                                    <span>Rp <?= $money($order['total_amount']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
