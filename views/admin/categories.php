<?php
$pageTitle = $pageTitle ?? 'Kelola Kategori';
$categories = $categories ?? [];
$category = $category ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$action = !empty($category['id']) ? '/admin/categories/update' : '/admin/categories/create';
ob_start();
?>
<div class="container-fluid py-2">
    <h1 class="section-title h2 mb-4">Kelola Kategori Menu</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="cozy-card p-4">
                <h2 class="h5 mb-3"><?= !empty($category['id']) ? 'Ubah Kategori' : 'Tambah Kategori Baru' ?></h2>
                <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if (!empty($category['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="name">Nama Kategori</label>
                        <input class="form-control" id="name" name="name" maxlength="100" value="<?= $escape($category['name'] ?? '') ?>" placeholder="Contoh: Coffee, Desserts..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi singkat kelompok menu..."><?= $escape($category['description'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i><?= !empty($category['id']) ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
                        <?php if (!empty($category['id'])): ?>
                            <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/categories">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Daftar Kategori Menu</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nama Kategori</th><th>Deskripsi</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($categories as $item): ?>
                            <tr>
                                <td class="fw-semibold"><?= $escape($item['name']) ?></td>
                                <td><?= $escape($item['description'] ?? '') ?></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee me-1" href="<?= $escape($baseUrl) ?>/admin/categories/edit?id=<?= (int) $item['id'] ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Ubah</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/categories/delete" onsubmit="return confirm('Hapus kategori ini? Kategori yang memiliki menu tidak dapat dihapus.');">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$categories): ?><tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori yang terdaftar.</td></tr><?php endif; ?>
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
