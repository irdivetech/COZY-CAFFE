<?php
$pageTitle = $pageTitle ?? 'Operating Hours';
$hours = $hours ?? [];
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];
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
        <form method="post" action="<?= $escape($baseUrl) ?>/admin/operating-hours/update" class="p-3 p-md-4">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Hari</th><th>Jam buka</th><th>Jam tutup</th><th>Libur</th></tr></thead>
                    <tbody>
                    <?php foreach ($days as $day => $label): ?>
                        <?php $hour = $hours[$day] ?? ['open_time' => '08:00', 'close_time' => '22:00', 'is_closed' => false]; ?>
                        <tr>
                            <th scope="row"><?= $escape($label) ?></th>
                            <td><input class="form-control" type="time" name="hours[<?= $day ?>][open_time]" value="<?= $escape($hour['open_time']) ?>" required></td>
                            <td><input class="form-control" type="time" name="hours[<?= $day ?>][close_time]" value="<?= $escape($hour['close_time']) ?>" required></td>
                            <td>
                                <input type="hidden" name="hours[<?= $day ?>][is_closed]" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" id="closed-<?= $day ?>" type="checkbox" name="hours[<?= $day ?>][is_closed]" value="1" <?= !empty($hour['is_closed']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="closed-<?= $day ?>">Closed</label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-coffee" type="submit">Save Operating Hours</button>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
