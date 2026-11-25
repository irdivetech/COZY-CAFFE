<?php
$pageTitle = $pageTitle ?? 'Reservation Closures';
$closures = $closures ?? [];
$closure = $closure ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$editing = !empty($closure['id']);
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
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0"><?= $editing ? 'Edit Closure' : 'Add Closure' ?></h2></div>
                <form method="post" action="<?= $escape($baseUrl) ?>/admin/closures/<?= $editing ? 'update' : 'create' ?>" class="p-3">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $closure['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="start_date">Start date</label>
                        <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($closure['start_date'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="end_date">End date</label>
                        <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($closure['end_date'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reason">Reason</label>
                        <input class="form-control" id="reason" name="reason" maxlength="255" value="<?= $escape($closure['reason'] ?? '') ?>" required>
                    </div>
                    <button class="btn btn-coffee" type="submit"><?= $editing ? 'Save Changes' : 'Add Closure' ?></button>
                    <?php if ($editing): ?><a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/closures">Cancel</a><?php endif; ?>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Closed Periods</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Start</th><th>End</th><th>Reason</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($closures as $item): ?>
                            <tr>
                                <td><?= $escape($item['start_date']) ?></td>
                                <td><?= $escape($item['end_date']) ?></td>
                                <td><?= $escape($item['reason']) ?></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/closures/edit?id=<?= (int) $item['id'] ?>">Edit</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/closures/delete" onsubmit="return confirm('Delete this reservation closure?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$closures): ?><tr><td colspan="4" class="text-center text-muted py-4">No reservation closures configured.</td></tr><?php endif; ?>
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
