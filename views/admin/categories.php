<?php
$pageTitle = $pageTitle ?? 'Manage Categories';
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
    <h1 class="section-title h2 mb-4"><?= $escape($pageTitle) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0"><?= !empty($category['id']) ? 'Edit Category' : 'Add Category' ?></h2></div>
                <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>" class="p-3">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if (!empty($category['id'])): ?>
                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="name">Name</label>
                        <input class="form-control" id="name" name="name" maxlength="100" value="<?= $escape($category['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= $escape($category['description'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-coffee" type="submit"><?= !empty($category['id']) ? 'Save Changes' : 'Add Category' ?></button>
                    <?php if (!empty($category['id'])): ?>
                        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/categories">Cancel</a>
                    <?php endif; ?>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Categories</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Name</th><th>Description</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($categories as $item): ?>
                            <tr>
                                <td class="fw-semibold"><?= $escape($item['name']) ?></td>
                                <td><?= $escape($item['description'] ?? '') ?></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/categories/edit?id=<?= (int) $item['id'] ?>">Edit</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/categories/delete" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$categories): ?><tr><td colspan="3" class="text-center text-muted py-4">No categories yet.</td></tr><?php endif; ?>
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
