<?php
$pageTitle = $pageTitle ?? 'Add Menu';
$menu = $menu ?? [];
$categories = $categories ?? [];
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$editing = !empty($menu['id']);
ob_start();
?>
<div class="container-fluid py-2" style="max-width: 1000px;">
    <h1 class="section-title h2 mb-4"><?= $escape($pageTitle) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <section class="cozy-card">
        <form method="post" enctype="multipart/form-data" action="<?= $escape($baseUrl) ?>/admin/menus/<?= $editing ? 'update' : 'create' ?>" class="p-4">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $menu['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Menu name</label>
                    <input class="form-control" id="name" name="name" maxlength="150" value="<?= $escape($menu['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (string) ($menu['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= $escape($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="price">Price (Rp)</label>
                    <input class="form-control" id="price" name="price" type="number" min="0.01" max="99999999.99" step="0.01" value="<?= $escape((string) ($menu['price'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="image">Menu photo (JPG, PNG, WEBP; max 2MB)</label>
                    <input class="form-control" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if (!empty($menu['image'])): ?>
                        <div class="small text-muted mt-2">Current photo: <?= $escape(basename($menu['image'])) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= $escape($menu['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" id="is_available" name="is_available" type="checkbox" value="1" <?= !array_key_exists('is_available', $menu) || !empty($menu['is_available']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_available">Available to customers</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-coffee" type="submit"><?= $editing ? 'Save Changes' : 'Add Menu' ?></button>
                    <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/menus">Cancel</a>
                </div>
            </div>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
