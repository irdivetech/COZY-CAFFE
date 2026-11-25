<?php
$pageTitle = $pageTitle ?? 'Daftar Akun';
$errors = $errors ?? [];
$name = $name ?? '';
$email = $email ?? '';
$phone = $phone ?? '';
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($pageTitle) ?> - CozyCafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $escape($baseUrl) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <section class="cozy-card w-100" style="max-width: 500px;">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <a class="brand-text fs-3 text-decoration-none" href="<?= $escape($baseUrl) ?>/">CozyCafe</a>
                <p class="text-muted mb-0 mt-2">Daftar Akun Pelanggan Baru</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= $escape($baseUrl) ?>/register">
                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                <div class="mb-3">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input class="form-control" id="name" name="name" type="text" maxlength="100" autocomplete="name" placeholder="Nama Anda" value="<?= $escape($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Alamat Email</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="191" autocomplete="email" placeholder="nama@email.com" value="<?= $escape($email) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Nomor Telepon (opsional)</label>
                    <input class="form-control" id="phone" name="phone" type="tel" maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" value="<?= $escape($phone) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Kata Sandi (minimal 8 karakter)</label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
                </div>
                <button class="btn btn-coffee w-100" type="submit">Daftar Sekarang</button>
            </form>
            <p class="text-center text-muted mt-4 mb-0">
                Sudah punya akun? <a href="<?= $escape($baseUrl) ?>/login" class="text-decoration-none">Masuk di sini</a>
            </p>
        </div>
    </section>
</main>
<footer class="customer-footer py-3 text-center"><small class="text-muted">A Cozy Place, A Better Taste</small></footer>
</body>
</html>
