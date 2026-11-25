<?php
$pageTitle = $pageTitle ?? 'Manage Menu';
$menus = $menus ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="section-title h2 mb-0"><?= $escape($pageTitle) ?></h1>
        <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/admin/menus/new"><i class="fa-solid fa-plus me-2"></i>Add Menu</a>
    </div>
    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Menu Items</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Menu</th><th>Category</th><th>Price</th><th>Availability</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($menus as $menu): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($menu['image'])): ?>
                                    <img src="<?= $escape($baseUrl) ?>/uploads/<?= rawurlencode(basename($menu['image'])) ?>" alt="" width="56" height="56" class="rounded object-fit-cover">
                                <?php endif; ?>
                                <span class="fw-semibold"><?= $escape($menu['name']) ?></span>
                            </div>
                        </td>
                        <td><?= $escape($menu['category_name']) ?></td>
                        <td>Rp <?= number_format((float) $menu['price'], 2, ',', '.') ?></td>
                        <td><span class="badge-status <?= $menu['is_available'] ? 'badge-confirmed' : 'badge-cancelled' ?>"><?= $menu['is_available'] ? 'Available' : 'Unavailable' ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/menus/edit?id=<?= (int) $menu['id'] ?>">Edit</a>
                            <?php if ($menu['is_available']): ?>
                                <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/menus/disable" onsubmit="return confirm('Deactivate this menu item?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $menu['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$menus): ?><tr><td colspan="5" class="text-center text-muted py-4">No menu items yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
