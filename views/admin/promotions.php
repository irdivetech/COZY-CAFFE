<?php
$pageTitle = $pageTitle ?? 'Manage Promotions';
$promotions = $promotions ?? [];
$menuOptions = $menuOptions ?? [];
$formData = $formData ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$editing = !empty($formData['id']);
$selectedMenus = array_map('intval', $formData['menu_ids'] ?? []);
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="section-title h2 mb-0"><?= $escape($pageTitle) ?></h1>
        <?php if (!$formData): ?>
            <a class="btn btn-coffee" href="<?= $escape($baseUrl) ?>/admin/promotions/new"><i class="fa-solid fa-plus me-2"></i>Add Promotion</a>
        <?php endif; ?>
    </div>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?php if ($formData): ?>
        <section class="cozy-card mb-4">
            <div class="cozy-card-header p-3"><h2 class="h5 mb-0"><?= $editing ? 'Edit Promotion' : 'Add Promotion' ?></h2></div>
            <form method="post" action="<?= $escape($baseUrl) ?>/admin/promotions/<?= $editing ? 'update' : 'create' ?>" class="p-3 p-md-4">
                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $formData['id'] ?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Promotion name</label>
                        <input class="form-control" id="name" name="name" maxlength="150" value="<?= $escape($formData['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="discount_type">Discount type</label>
                        <select class="form-select" id="discount_type" name="discount_type" required>
                            <option value="percentage" <?= ($formData['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= ($formData['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed amount</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="discount_value">Discount value</label>
                        <input class="form-control" id="discount_value" name="discount_value" type="number" min="0.01" step="0.01" value="<?= $escape((string) ($formData['discount_value'] ?? '')) ?>" required>
                        <div class="form-text">Percentage must not exceed 100.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="start_date">Start date</label>
                        <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($formData['start_date'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="end_date">End date</label>
                        <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($formData['end_date'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $escape($formData['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <fieldset>
                            <legend class="form-label">Eligible menus</legend>
                            <div class="row g-2">
                                <?php foreach ($menuOptions as $menu): ?>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-check">
                                            <input class="form-check-input" id="promotion-menu-<?= (int) $menu['id'] ?>" type="checkbox" name="menu_ids[]" value="<?= (int) $menu['id'] ?>" <?= in_array((int) $menu['id'], $selectedMenus, true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="promotion-menu-<?= (int) $menu['id'] ?>">
                                                <?= $escape($menu['name']) ?>
                                                <?php if (!$menu['is_available']): ?><span class="text-muted small">(unavailable)</span><?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (!$menuOptions): ?><div class="text-muted">Add menu items before creating a promotion.</div><?php endif; ?>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-12">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" <?= !array_key_exists('is_active', $formData) || !empty($formData['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-coffee" type="submit"><?= $editing ? 'Save Changes' : 'Add Promotion' ?></button>
                        <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/promotions">Cancel</a>
                    </div>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Promotions</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Discount</th><th>Period</th><th>Menus</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($promotions as $promotion): ?>
                    <tr>
                        <td class="fw-semibold"><?= $escape($promotion['name']) ?></td>
                        <td><?= $promotion['discount_type'] === 'percentage' ? $escape(rtrim(rtrim(number_format((float) $promotion['discount_value'], 2, '.', ''), '0'), '.')) . '%' : 'Rp ' . number_format((float) $promotion['discount_value'], 0, ',', '.') ?></td>
                        <td><?= $escape($promotion['start_date']) ?> – <?= $escape($promotion['end_date']) ?></td>
                        <td><?= (int) $promotion['menu_count'] ?></td>
                        <td><span class="badge-status <?= $promotion['is_active'] ? 'badge-confirmed' : 'badge-cancelled' ?>"><?= $promotion['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/promotions/edit?id=<?= (int) $promotion['id'] ?>">Edit</a>
                            <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/promotions/delete" onsubmit="return confirm('Delete this promotion?');">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $promotion['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$promotions): ?><tr><td colspan="6" class="text-center text-muted py-4">No promotions yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
