<?php
$isEdit = !empty($menu['id']);
$pageTitle = $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru';
$menu = $menu ?? [];
$categories = $categories ?? [];
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$action = $isEdit ? '/admin/menus/update' : '/admin/menus/create';
ob_start();
?>
<div class="container-fluid py-2" style="max-width: 850px;">
    <h1 class="section-title h2 mb-4"><?= $escape($pageTitle) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <section class="cozy-card p-4">
        <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int) $menu['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold" for="name">Nama Menu</label>
                    <input class="form-control" id="name" name="name" maxlength="150" value="<?= $escape($menu['name'] ?? '') ?>" placeholder="Contoh: Caramel Macchiato" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold" for="category_id">Kategori Menu</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($menu['category_id'] ?? 0)) === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= $escape($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">Deskripsi Menu</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Jelaskan bahan, rasa, atau keunikan menu..."><?= $escape($menu['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="price">Harga (Rp)</label>
                    <input class="form-control" id="price" name="price" type="number" step="0.01" min="0" max="99999999.99" value="<?= $escape($menu['price'] ?? '') ?>" placeholder="25000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="image">Foto Menu (JPG/PNG/WEBP, Maks 2MB)</label>
                    <input class="form-control" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($menu['image'])): ?>
                        <div class="small text-muted mt-1">Foto saat ini: <?= $escape(basename($menu['image'])) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" <?= !isset($menu['is_available']) || $menu['is_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_available">Tersedia untuk dipesan pelanggan</label>
                    </div>
                </div>
                <div class="col-12 mt-4 d-flex gap-2">
                    <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Menu</button>
                    <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/menus">Batal</a>
                </div>
            </div>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
