<?php
$pageTitle = $pageTitle ?? 'Kelola Menu';
$menus = $menus ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$money = static fn ($value): string => number_format((float) $value, 2, ',', '.');
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="section-title h2 mb-0">Kelola Menu</h1>
            <p class="text-muted small mb-0">Menu yang aktif dan tersedia dapat dilihat dan dipesan langsung oleh pelanggan.</p>
        </div>
        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/admin/menus/new"><i class="fa-solid fa-plus me-1"></i>Tambah Menu Baru</a>
    </div>

    <section class="cozy-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Foto</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Ketersediaan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($menus as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($item['image'])) ?>" alt="" width="56" height="56" class="rounded object-fit-cover">
                            <?php else: ?>
                                <div class="rounded bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 56px; height: 56px;"><i class="fa-solid fa-mug-hot"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= $escape($item['name']) ?></div>
                            <small class="text-muted"><?= $escape($item['description'] ?? '') ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= $escape($item['category_name']) ?></span></td>
                        <td class="fw-semibold text-coffee">Rp <?= $money($item['price']) ?></td>
                        <td>
                            <?php if ($item['is_available']): ?>
                                <span class="badge text-bg-success">Tersedia</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger">Habis / Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-coffee me-1" href="<?= $escape($baseUrl) ?>/admin/menus/edit?id=<?= (int) $item['id'] ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Ubah</a>
                            <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/menus/toggle" onsubmit="return confirm('<?= $item['is_available'] ? 'Nonaktifkan menu ini agar tidak tampil ke pelanggan?' : 'Aktifkan kembali menu ini agar dapat dipesan pelanggan?' ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-sm <?= $item['is_available'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                                    <i class="fa-solid <?= $item['is_available'] ? 'fa-ban' : 'fa-check' ?> me-1"></i><?= $item['is_available'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$menus): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada menu yang dibuat. Silakan klik Tambah Menu Baru.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
