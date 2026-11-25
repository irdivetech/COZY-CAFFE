<?php
$pageTitle = $pageTitle ?? 'Register';
$errors = $errors ?? [];
$name = $name ?? '';
$email = $email ?? '';
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
<body class="d-flex flex-column">
<main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <section class="cozy-card w-100" style="max-width: 500px;">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <a class="brand-text fs-3" href="<?= $escape($baseUrl) ?>/">CozyCafe</a>
                <p class="text-muted mb-0 mt-2">Buat akun customer</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= $escape($baseUrl) ?>/register">
                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                <div class="mb-3">
                    <label class="form-label" for="name">Nama</label>
                    <input class="form-control" id="name" name="name" type="text" maxlength="100" autocomplete="name" value="<?= $escape($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="191" autocomplete="email" value="<?= $escape($email) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password <span class="text-muted">(minimal 8 karakter)</span></label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <button class="btn btn-coffee w-100" type="submit">Daftar</button>
            </form>
            <p class="text-center text-muted mt-4 mb-0">
                Sudah punya akun? <a href="<?= $escape($baseUrl) ?>/login">Login</a>
            </p>
        </div>
    </section>
</main>
<footer class="customer-footer py-3 text-center"><small class="text-muted">A Cozy Place, A Better Taste</small></footer>
</body>
</html>
