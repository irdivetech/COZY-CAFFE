<?php
$pageTitle = $pageTitle ?? 'CozyCafe';
$content = $content ?? '';
$currentUser = $currentUser ?? (class_exists('AuthHelper') ? AuthHelper::currentUser() : null);
$cartCount = (int) ($cartCount ?? 0);
$unreadNotifications = (int) ($unreadNotifications ?? 0);
$notificationItems = [];
if (($currentUser['role'] ?? '') === 'customer') {
    $unreadNotifications = NotificationHelper::unreadCount((int) $currentUser['id']);
    $notificationItems = NotificationHelper::forUser((int) $currentUser['id'], 5);
}
$flashMessages = class_exists('FlashHelper') ? FlashHelper::get() : [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
$link = static fn (string $path): string => ($baseUrl ?: '') . '/' . ltrim($path, '/');
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
<nav class="navbar navbar-expand-lg customer-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand brand-text" href="<?= $escape($link('')) ?>">CozyCafe</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNavigation" aria-controls="customerNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="customerNavigation">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= $escape($link('')) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $escape($link('menu')) ?>">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $escape($link('reservation')) ?>">Reservation</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $escape($link('orders')) ?>">My Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $escape($link('bookings')) ?>">My Bookings</a></li>
            </ul>
            <div class="d-flex align-items-center gap-1">
                <a class="navbar-icon-link" href="<?= $escape($link('cart')) ?>" aria-label="Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="navbar-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <button class="navbar-icon-link border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="navbar-badge"><?= $unreadNotifications > 99 ? '99+' : $unreadNotifications ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2 notification-dropdown">
                        <div class="d-flex justify-content-between align-items-center px-2 py-1">
                            <strong>Notifications</strong>
                            <a class="small" href="<?= $escape($link('notifications')) ?>">View all</a>
                        </div>
                        <?php if ($unreadNotifications > 0): ?>
                            <form method="post" action="<?= $escape($link('notifications/read-all')) ?>" class="px-2 py-1">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <button class="btn btn-link btn-sm p-0" type="submit">Mark all as read</button>
                            </form>
                        <?php endif; ?>
                        <?php if (!$notificationItems): ?>
                            <div class="dropdown-item-text small text-muted">No notifications yet.</div>
                        <?php else: ?>
                            <?php foreach ($notificationItems as $notification): ?>
                                <div class="dropdown-item-text notification-item <?= empty($notification['is_read']) ? 'notification-unread' : '' ?>">
                                    <a class="d-block text-reset text-decoration-none" href="<?= $escape(NotificationHelper::internalLink($notification['link'], $baseUrl, '/notifications')) ?>">
                                        <span class="d-block fw-semibold"><?= $escape($notification['title']) ?></span>
                                        <span class="small text-muted"><?= $escape($notification['message']) ?></span>
                                    </a>
                                    <?php if (empty($notification['is_read'])): ?>
                                        <form method="post" action="<?= $escape($link('notifications/read')) ?>">
                                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                            <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                                            <button class="btn btn-link btn-sm p-0" type="submit">Mark as read</button>
                                        </form>
                                    <?php endif; ?>
                                    <small class="text-muted"><?= $escape($notification['created_at']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $escape($currentUser['name'] ?? 'Profile') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $escape($link('profile')) ?>">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= $escape($link('logout')) ?>">
                                    <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                    <button class="dropdown-item" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-outline-coffee btn-sm ms-2" href="<?= $escape($link('login')) ?>">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="flex-grow-1">
    <?php foreach ($flashMessages as $flash): ?>
        <?php $flashType = ($flash['type'] ?? '') === 'success' ? 'success' : 'danger'; ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flashType ?> flash-alert flash-<?= $escape($flashType) ?>" role="alert">
                <?= $escape($flash['message'] ?? '') ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<footer class="customer-footer py-4">
    <div class="container text-center">
        <div class="brand-text mb-1">CozyCafe</div>
        <small class="text-muted">A Cozy Place, A Better Taste</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
</body>
</html>
