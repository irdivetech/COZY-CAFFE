<?php
$pageTitle = $pageTitle ?? 'Profil Saya';
$user = $user ?? [];
$errors = $errors ?? [];
$success = $success ?? null;
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5" style="max-width: 700px;">
    <h1 class="section-title h2 mb-4">Profil Saya</h1>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= $escape($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <section class="cozy-card">
        <div class="cozy-card-header p-3"><h2 class="h5 mb-0">Informasi Akun</h2></div>
        <form method="post" action="<?= $escape($baseUrl) ?>/profile" class="p-4">
            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="100" value="<?= $escape($user['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Alamat Email</label>
                <input class="form-control" id="email" type="email" value="<?= $escape($user['email'] ?? '') ?>" readonly>
                <small class="text-muted">Email tidak dapat diubah.</small>
            </div>
            <div class="mb-4">
                <label class="form-label" for="phone">Nomor Telepon</label>
                <input class="form-control" id="phone" name="phone" type="tel" maxlength="30" value="<?= $escape($user['phone'] ?? '') ?>">
            </div>
            <hr>
            <h3 class="h6 mb-3">Ganti Kata Sandi (opsional)</h3>
            <div class="mb-3">
                <label class="form-label" for="current_password">Kata Sandi Lama</label>
                <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi lama">
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password">Kata Sandi Baru</label>
                <input class="form-control" id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
            </div>
            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
            </div>
            <button class="btn btn-coffee" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button>
        </form>
    </section>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
