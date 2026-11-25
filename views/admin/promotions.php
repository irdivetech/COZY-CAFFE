<?php
$pageTitle = $pageTitle ?? 'Kelola Promosi';
$promotions = $promotions ?? [];
$promotion = $promotion ?? null;
$menus = $menus ?? [];
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$isEdit = !empty($promotion['id']);
$action = $isEdit ? '/admin/promotions/update' : '/admin/promotions/create';
$selectedMenus = $promotion['menu_ids'] ?? [];
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Promosi & Diskon</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <section class="cozy-card p-4">
                <h2 class="h5 mb-3"><?= $isEdit ? 'Ubah Data Promosi' : 'Tambah Promosi Baru' ?></h2>
                <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $promotion['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="name">Nama Promosi</label>
                        <input class="form-control" id="name" name="name" maxlength="150" value="<?= $escape($promotion['name'] ?? '') ?>" placeholder="Contoh: Diskon Kopi Pagi 20%" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Ketentuan atau keterangan promo..."><?= $escape($promotion['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="discount_type">Jenis Diskon</label>
                            <select class="form-select" id="discount_type" name="discount_type" required>
                                <option value="percentage" <?= ($promotion['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Persentase (%)</option>
                                <option value="fixed" <?= ($promotion['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="discount_value">Nilai Diskon</label>
                            <input class="form-control" id="discount_value" name="discount_value" type="number" step="0.01" min="0.01" max="99999999.99" value="<?= $escape($promotion['discount_value'] ?? '') ?>" placeholder="20 atau 10000" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="start_date">Tanggal Mulai</label>
                            <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($promotion['start_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="end_date">Tanggal Berakhir</label>
                            <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($promotion['end_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !isset($promotion['is_active']) || $promotion['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Promosi Aktif</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Pilih Menu yang Mendapat Promo</label>
                        <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                            <?php foreach ($menus as $m): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="menu_ids[]" value="<?= (int) $m['id'] ?>" id="menu-<?= (int) $m['id'] ?>" <?= in_array((int) $m['id'], $selectedMenus, true) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="menu-<?= (int) $m['id'] ?>">
                                        <?= $escape($m['name']) ?> <span class="text-muted">(<?= $escape($m['category_name']) ?>)</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Pilih minimal 1 menu.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Promosi' ?></button>
                        <?php if ($isEdit): ?>
                            <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/promotions">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>

        <div class="col-lg-7">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Daftar Promosi</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nama Promosi</th><th>Besar Diskon</th><th>Periode Berlaku</th><th>Menu Terkait</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($promotions as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-coffee"><?= $escape($item['name']) ?></div>
                                    <small class="text-muted"><?= $escape($item['description'] ?? '') ?></small>
                                </td>
                                <td class="fw-bold">
                                    <?= $item['discount_type'] === 'percentage'
                                        ? $escape(rtrim(rtrim(number_format((float) $item['discount_value'], 2, '.', ''), '0'), '.')) . '%'
                                        : 'Rp ' . number_format((float) $item['discount_value'], 0, ',', '.') ?>
                                </td>
                                <td class="small">
                                    <?= $escape($item['start_date']) ?><br>s.d <?= $escape($item['end_date']) ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= (int) $item['menu_count'] ?> menu</span></td>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="badge text-bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee me-1" href="<?= $escape($baseUrl) ?>/admin/promotions/edit?id=<?= (int) $item['id'] ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Ubah</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/promotions/delete" onsubmit="return confirm('Hapus promosi ini?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$promotions): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada promosi yang dibuat.</td></tr><?php endif; ?>
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
