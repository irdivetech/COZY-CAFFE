<?php
$pageTitle = $pageTitle ?? 'Manage Tables';
$tables = $tables ?? [];
$formData = $formData ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$editing = !empty($formData['id']);
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
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0"><?= $editing ? 'Edit Table' : 'Add Table' ?></h2></div>
                <form method="post" action="<?= $escape($baseUrl) ?>/admin/tables/<?= $editing ? 'update' : 'create' ?>" class="p-3">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $formData['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="table_number">Table number</label>
                        <input class="form-control" id="table_number" name="table_number" maxlength="20" value="<?= $escape($formData['table_number'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="capacity">Capacity</label>
                        <input class="form-control" id="capacity" name="capacity" type="number" min="1" max="1000" value="<?= $escape((string) ($formData['capacity'] ?? '2')) ?>" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" <?= !array_key_exists('is_active', $formData ?? []) || !empty($formData['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <button class="btn btn-coffee" type="submit"><?= $editing ? 'Save Changes' : 'Add Table' ?></button>
                    <?php if ($editing): ?><a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/tables">Cancel</a><?php endif; ?>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Tables</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Table</th><th>Capacity</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td class="fw-semibold"><?= $escape($table['table_number']) ?></td>
                                <td><?= (int) $table['capacity'] ?> guests</td>
                                <td><span class="badge-status <?= $table['is_active'] ? 'badge-confirmed' : 'badge-cancelled' ?>"><?= $table['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/tables/edit?id=<?= (int) $table['id'] ?>">Edit</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/tables/toggle">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $table['id'] ?>">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $table['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$tables): ?><tr><td colspan="4" class="text-center text-muted py-4">No tables yet.</td></tr><?php endif; ?>
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
