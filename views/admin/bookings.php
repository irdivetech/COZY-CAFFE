<?php
$pageTitle = $pageTitle ?? 'Kelola Reservasi';
$bookings = $bookings ?? [];
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$statusClass = static fn (string $status): string => match ($status) {
    'Confirmed' => 'text-bg-primary',
    'Completed' => 'text-bg-success',
    'Cancelled' => 'text-bg-danger',
    default => 'text-bg-secondary',
};
$bookingStatusLabel = static fn (string $status): string => match ($status) {
    'Confirmed' => 'Dikonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan',
    default => $status,
};
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Reservasi Meja</h1>
    <?php if ($errors): ?><div class="alert alert-danger" role="alert"><?php foreach ($errors as $error): ?><div><?= $escape($error) ?></div><?php endforeach; ?></div><?php endif; ?>

    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Pelanggan</th><th>Meja</th><th>Tanggal & Jam (2 Jam)</th><th>Jumlah Tamu</th><th>Catatan</th><th>Status</th><th class="text-end">Aksi Status</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= $escape($booking['customer_name']) ?></div>
                            <small class="text-muted"><?= $escape($booking['customer_email']) ?> · <?= $escape($booking['customer_phone'] ?? '-') ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark border">Meja <?= $escape($booking['table_number']) ?></span></td>
                        <td>
                            <div><i class="fa-regular fa-calendar me-1 text-coffee"></i><?= $escape($booking['booking_date']) ?></div>
                            <small class="text-muted"><?= $escape(substr($booking['start_time'], 0, 5)) ?> - <?= $escape(substr($booking['end_time'], 0, 5)) ?></small>
                        </td>
                        <td><?= (int) $booking['guest_count'] ?> orang</td>
                        <td><?= $booking['notes'] ? $escape($booking['notes']) : '<span class="text-muted fst-italic">-</span>' ?></td>
                        <td><span class="badge <?= $statusClass($booking['status']) ?>"><?= $bookingStatusLabel($booking['status']) ?></span></td>
                        <td class="text-end text-nowrap">
                            <?php if ($booking['status'] === 'Confirmed'): ?>
                                <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/bookings/status">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <input type="hidden" name="status" value="Completed">
                                    <button class="btn btn-sm btn-outline-success me-1" type="submit"><i class="fa-solid fa-check me-1"></i>Selesai</button>
                                </form>
                                <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/bookings/status" onsubmit="return confirm('Batalkan reservasi ini? Meja akan kembali tersedia untuk pelanggan lain.');">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <input type="hidden" name="status" value="Cancelled">
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Batalkan</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Tidak ada aksi</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$bookings): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada data reservasi meja.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
