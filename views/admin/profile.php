<?php
$pageTitle = $pageTitle ?? 'Admin Profile';
$user = $user ?? [];
$errors = $errors ?? [];
$success = $success ?? null;
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="container-fluid py-2" style="max-width: 900px;">
    <h1 class="section-title h2 mb-4">Admin Profile</h1>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= $escape($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Informasi akun</h2></div>
        <form method="post" action="<?= $escape(rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '')), '/')) ?>/admin/profile" class="p-4">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="name">Nama</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="100" value="<?= $escape($user['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" id="email" type="email" value="<?= $escape($user['email'] ?? '') ?>" readonly>
            </div>
            <div class="mb-4">
                <label class="form-label" for="phone">Nomor telepon</label>
                <input class="form-control" id="phone" name="phone" type="tel" maxlength="30" value="<?= $escape($user['phone'] ?? '') ?>">
            </div>
            <hr>
            <h3 class="h6 mb-3">Ganti password (opsional)</h3>
            <div class="mb-3">
                <label class="form-label" for="current_password">Password lama</label>
                <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password">Password baru</label>
                <input class="form-control" id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Konfirmasi password baru</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
            </div>
            <button class="btn btn-coffee" type="submit">Simpan perubahan</button>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
