<?php
$pageTitle = $pageTitle ?? 'Reservasi Saya';
$bookings = $bookings ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$statusBadge = static fn (string $status): string => match ($status) {
    'Completed' => 'text-bg-success',
    'Confirmed' => 'text-bg-primary',
    'Cancelled' => 'text-bg-danger',
    default => 'text-bg-secondary',
};
$statusLabel = static fn (string $status): string => match ($status) {
    'Confirmed' => 'Dikonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan',
    default => $status,
};
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Jadwal Meja</p>
            <h1 class="section-title h2 mb-0">Riwayat Reservasi Saya</h1>
        </div>
        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/reservation"><i class="fa-solid fa-calendar-plus me-2"></i>Buat Reservasi Baru</a>
    </div>

    <?php if (!$bookings): ?>
        <div class="cozy-card p-5 text-center">
            <i class="fa-solid fa-chair fa-3x text-muted mb-3"></i>
            <h2 class="h5">Belum Ada Riwayat Reservasi</h2>
            <p class="text-muted">Anda belum memiliki jadwal reservasi meja di CozyCafe.</p>
            <a class="btn btn-coffee mt-2" href="<?= $escape($baseUrl) ?>/reservation">Reservasi Meja Sekarang</a>
        </div>
    <?php else: ?>
        <div class="cozy-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam (Durasi 2 Jam)</th>
                            <th>Nomor Meja</th>
                            <th>Jumlah Tamu</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="fw-semibold"><i class="fa-regular fa-calendar me-1 text-coffee"></i><?= $escape($b['booking_date']) ?></td>
                            <td><?= $escape(substr($b['start_time'], 0, 5)) ?> - <?= $escape(substr($b['end_time'], 0, 5)) ?></td>
                            <td><span class="badge bg-light text-dark border">Meja <?= $escape($b['table_number']) ?></span></td>
                            <td><?= (int) $b['guest_count'] ?> orang</td>
                            <td><span class="badge <?= $statusBadge($b['status']) ?>"><?= $statusLabel($b['status']) ?></span></td>
                            <td><?= $b['notes'] ? $escape($b['notes']) : '<span class="text-muted fst-italic">-</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
