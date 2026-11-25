<?php
$pageTitle = $pageTitle ?? 'Kelola Meja';
$tables = $tables ?? [];
$table = $table ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$action = !empty($table['id']) ? '/admin/tables/update' : '/admin/tables/create';
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Meja Cafe</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="cozy-card p-4">
                <h2 class="h5 mb-3"><?= !empty($table['id']) ? 'Ubah Data Meja' : 'Tambah Meja Baru' ?></h2>
                <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if (!empty($table['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int) $table['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="table_number">Nomor / Nama Meja</label>
                        <input class="form-control" id="table_number" name="table_number" maxlength="20" value="<?= $escape($table['table_number'] ?? '') ?>" placeholder="Contoh: T01, T02, VIP-1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="capacity">Kapasitas (Jumlah Kursi/Orang)</label>
                        <input class="form-control" id="capacity" name="capacity" type="number" min="1" max="50" value="<?= (int) ($table['capacity'] ?? 2) ?>" required>
                    </div>
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !isset($table['is_active']) || $table['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif dan dapat dipilih untuk reservasi</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Meja</button>
                        <?php if (!empty($table['id'])): ?>
                            <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/tables">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Daftar Meja Cafe</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nomor Meja</th><th>Kapasitas</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($tables as $t): ?>
                            <tr>
                                <td class="fw-bold fs-6 text-coffee">Meja <?= $escape($t['table_number']) ?></td>
                                <td><?= (int) $t['capacity'] ?> orang</td>
                                <td>
                                    <?php if ($t['is_active']): ?>
                                        <span class="badge text-bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee me-1" href="<?= $escape($baseUrl) ?>/admin/tables/edit?id=<?= (int) $t['id'] ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Ubah</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/tables/toggle">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <button class="btn btn-sm <?= $t['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                                            <?= $t['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$tables): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada meja yang dikonfigurasi.</td></tr><?php endif; ?>
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
