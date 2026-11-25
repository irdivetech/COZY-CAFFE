<?php
$pageTitle = $pageTitle ?? 'Penutupan Reservasi';
$closures = $closures ?? [];
$closure = $closure ?? null;
$errors = $errors ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$isEdit = !empty($closure['id']);
$action = $isEdit ? '/admin/closures/update' : '/admin/closures/create';
ob_start();
?>
<div class="container-fluid py-2">
    <div class="mb-4">
        <h1 class="section-title h2 mb-1">Penutupan Reservasi Meja</h1>
        <p class="text-muted">Tutup sementara layanan reservasi pada rentang tanggal tertentu (misalnya hari libur nasional, pemeliharaan/renovasi, atau acara privat).</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <section class="cozy-card p-4">
                <h2 class="h5 mb-3"><?= $isEdit ? 'Ubah Periode Penutupan' : 'Tambah Periode Penutupan' ?></h2>
                <form method="post" action="<?= $escape($baseUrl) ?><?= $action ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $closure['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="start_date">Tanggal Mulai</label>
                        <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $escape($closure['start_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="end_date">Tanggal Selesai</label>
                        <input class="form-control" id="end_date" name="end_date" type="date" value="<?= $escape($closure['end_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="reason">Alasan Penutupan</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Contoh: Libur Hari Raya, Renovasi Dapur, Acara Privat..." required><?= $escape($closure['reason'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Penutupan' ?></button>
                        <?php if ($isEdit): ?>
                            <a class="btn btn-outline-coffee" href="<?= $escape($baseUrl) ?>/admin/closures">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>

        <div class="col-lg-8">
            <section class="cozy-card">
                <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Jadwal Penutupan Reservasi</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Alasan Penutupan</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        <?php foreach ($closures as $item): ?>
                            <tr>
                                <td class="fw-semibold"><i class="fa-regular fa-calendar me-1 text-danger"></i><?= $escape($item['start_date']) ?></td>
                                <td class="fw-semibold"><i class="fa-regular fa-calendar me-1 text-danger"></i><?= $escape($item['end_date']) ?></td>
                                <td><?= $escape($item['reason']) ?></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-coffee me-1" href="<?= $escape($baseUrl) ?>/admin/closures/edit?id=<?= (int) $item['id'] ?>"><i class="fa-solid fa-pen-to-square me-1"></i>Ubah</a>
                                    <form class="d-inline" method="post" action="<?= $escape($baseUrl) ?>/admin/closures/delete" onsubmit="return confirm('Hapus penutupan reservasi ini?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$closures): ?><tr><td colspan="4" class="text-center text-muted py-4">Tidak ada jadwal penutupan reservasi. Layanan reservasi dibuka normal.</td></tr><?php endif; ?>
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
