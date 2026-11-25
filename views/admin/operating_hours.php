<?php
$pageTitle = $pageTitle ?? 'Jam Operasional';
$hours = $hours ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$dayNames = [
    0 => 'Minggu',
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
];
ob_start();
?>
<div class="container-fluid py-2" style="max-width: 900px;">
    <div class="mb-4">
        <h1 class="section-title h2 mb-1">Jam Operasional Cafe</h1>
        <p class="text-muted">Atur jadwal buka, jam tutup, dan hari libur cafe. Sistem reservasi meja pelanggan akan otomatis mengacu pada pengaturan ini.</p>
    </div>

    <section class="cozy-card p-4">
        <form method="post" action="<?= $escape($baseUrl) ?>/admin/operating-hours/update">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Hari</th>
                            <th>Jam Buka</th>
                            <th>Jam Tutup</th>
                            <th class="text-center">Status Tutup / Libur</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($hours as $row): ?>
                        <?php $day = (int) $row['day_of_week']; ?>
                        <tr>
                            <td class="fw-semibold"><?= $escape($dayNames[$day] ?? ('Hari ' . $day)) ?></td>
                            <td>
                                <input class="form-control form-control-sm" type="time" name="hours[<?= $day ?>][open_time]" value="<?= $escape(substr($row['open_time'], 0, 5)) ?>" required style="max-width: 140px;">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="time" name="hours[<?= $day ?>][close_time]" value="<?= $escape(substr($row['close_time'], 0, 5)) ?>" required style="max-width: 140px;">
                            </td>
                            <td class="text-center">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="hours[<?= $day ?>][is_closed]" value="1" id="closed-<?= $day ?>" <?= !empty($row['is_closed']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="closed-<?= $day ?>">Tutup pada hari ini</label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Jam Operasional</button>
            </div>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
