<?php
$pageTitle = $pageTitle ?? 'Kelola Pelanggan';
$customers = $customers ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Pelanggan</h1>
    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Daftar Pelanggan Terdaftar</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nama Pelanggan</th><th>Email</th><th>Nomor Telepon</th><th>Total Pesanan</th><th>Total Reservasi</th><th>Terdaftar Sejak</th></tr></thead>
                <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td class="fw-semibold"><?= $escape($customer['name']) ?></td>
                        <td><?= $escape($customer['email']) ?></td>
                        <td><?= $escape($customer['phone'] ?? '-') ?></td>
                        <td><span class="badge bg-light text-dark border"><?= (int) $customer['total_orders'] ?> pesanan</span></td>
                        <td><span class="badge bg-light text-dark border"><?= (int) $customer['total_bookings'] ?> reservasi</span></td>
                        <td class="small text-muted"><i class="fa-regular fa-calendar me-1"></i><?= $escape($customer['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$customers): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada pelanggan terdaftar.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
