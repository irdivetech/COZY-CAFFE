<?php
$pageTitle = $pageTitle ?? 'Admin Dashboard';
$content = $content ?? '';
$currentUser = $currentUser ?? (class_exists('AuthHelper') ? AuthHelper::currentUser() : null);
$unreadNotifications = (int) ($unreadNotifications ?? 0);
$notificationItems = [];
if (($currentUser['role'] ?? '') === 'admin') {
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
    <title><?= $escape($pageTitle) ?> - CozyCafe Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $escape($baseUrl) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<aside class="admin-sidebar" id="adminSidebar">
    <div class="px-3 py-4 border-bottom border-light border-opacity-25">
        <a class="brand-text fs-5" href="<?= $escape($link('admin/dashboard')) ?>">CozyCafe Admin</a>
    </div>
    <nav class="nav flex-column py-3">
        <a class="nav-link" href="<?= $escape($link('admin/dashboard')) ?>"><i class="fa-solid fa-chart-line"></i>Dashboard</a>
        <a class="nav-link" href="<?= $escape($link('admin/menus')) ?>"><i class="fa-solid fa-utensils"></i>Manage Menu</a>
        <a class="nav-link" href="<?= $escape($link('admin/categories')) ?>"><i class="fa-solid fa-tags"></i>Manage Categories</a>
        <a class="nav-link" href="<?= $escape($link('admin/orders')) ?>"><i class="fa-solid fa-receipt"></i>Manage Orders</a>
        <a class="nav-link" href="<?= $escape($link('admin/bookings')) ?>"><i class="fa-solid fa-calendar-check"></i>Manage Bookings</a>
        <a class="nav-link" href="<?= $escape($link('admin/tables')) ?>"><i class="fa-solid fa-table-cells-large"></i>Manage Tables</a>
        <a class="nav-link" href="<?= $escape($link('admin/customers')) ?>"><i class="fa-solid fa-users"></i>Manage Customers</a>
        <a class="nav-link" href="<?= $escape($link('admin/promotions')) ?>"><i class="fa-solid fa-percent"></i>Manage Promotions</a>
        <a class="nav-link" href="<?= $escape($link('admin/operating-hours')) ?>"><i class="fa-solid fa-clock"></i>Operating Hours</a>
        <a class="nav-link" href="<?= $escape($link('admin/closures')) ?>"><i class="fa-solid fa-calendar-xmark"></i>Reservation Closures</a>
        <a class="nav-link" href="<?= $escape($link('admin/reports')) ?>"><i class="fa-solid fa-chart-column"></i>Reports</a>
        <a class="nav-link" href="<?= $escape($link('admin/profile')) ?>"><i class="fa-solid fa-user-gear"></i>Profile</a>
    </nav>
</aside>

<div class="admin-main">
    <header class="admin-topbar d-flex align-items-center justify-content-between px-3 px-lg-4">
        <button class="btn btn-outline-coffee d-lg-none" type="button" aria-controls="adminSidebar" aria-expanded="false" id="adminMenuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="h5 mb-0"><?= $escape($pageTitle) ?></h1>
        <div class="d-flex align-items-center gap-2">
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
                        <a class="small" href="<?= $escape($link('admin/notifications')) ?>">View all</a>
                    </div>
                    <?php if ($unreadNotifications > 0): ?>
                        <form method="post" action="<?= $escape($link('admin/notifications/read-all')) ?>" class="px-2 py-1">
                            <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                            <button class="btn btn-link btn-sm p-0" type="submit">Mark all as read</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!$notificationItems): ?>
                        <div class="dropdown-item-text small text-muted">No notifications yet.</div>
                    <?php else: ?>
                        <?php foreach ($notificationItems as $notification): ?>
                            <div class="dropdown-item-text notification-item <?= empty($notification['is_read']) ? 'notification-unread' : '' ?>">
                                <a class="d-block text-reset text-decoration-none" href="<?= $escape(NotificationHelper::internalLink($notification['link'], $baseUrl, '/admin/notifications')) ?>">
                                    <span class="d-block fw-semibold"><?= $escape($notification['title']) ?></span>
                                    <span class="small text-muted"><?= $escape($notification['message']) ?></span>
                                </a>
                                <?php if (empty($notification['is_read'])): ?>
                                    <form method="post" action="<?= $escape($link('admin/notifications/read')) ?>">
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
                        <?= $escape($currentUser['name'] ?? 'Admin') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $escape($link('admin/profile')) ?>">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="<?= $escape($link('logout')) ?>">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <button class="dropdown-item" type="submit">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="admin-content">
        <?php foreach ($flashMessages as $flash): ?>
            <?php $flashType = ($flash['type'] ?? '') === 'success' ? 'success' : 'danger'; ?>
            <div class="alert alert-<?= $flashType ?>" role="alert">
                <?= $escape($flash['message'] ?? '') ?>
            </div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
<script>
document.getElementById('adminMenuToggle')?.addEventListener('click', function () {
    document.getElementById('adminSidebar')?.classList.toggle('show');
});
</script>
</body>
</html>
