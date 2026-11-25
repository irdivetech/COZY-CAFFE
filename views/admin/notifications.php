<?php
$pageTitle = $pageTitle ?? 'Admin Notifications';
$notifications = $notifications ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<div class="container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="section-title h2 mb-0">Admin Notifications</h1>
        <?php if (array_filter($notifications, static fn (array $item): bool => empty($item['is_read']))): ?>
            <form method="post" action="<?= $escape($baseUrl) ?>/admin/notifications/read-all">
                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                <button class="btn btn-outline-coffee" type="submit">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if (!$notifications): ?>
        <div class="cozy-card p-5 text-center"><h2 class="h5">No notifications yet</h2></div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($notifications as $notification): ?>
                <article class="cozy-card p-4 <?= empty($notification['is_read']) ? 'notification-unread' : '' ?>">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h5 mb-1"><?= $escape($notification['title']) ?></h2>
                            <p class="mb-1"><?= nl2br($escape($notification['message'])) ?></p>
                            <small class="text-muted"><?= $escape($notification['created_at']) ?></small>
                            <div><a href="<?= $escape(NotificationHelper::internalLink($notification['link'], $baseUrl, '/admin/orders')) ?>">Open related page</a></div>
                        </div>
                        <?php if (empty($notification['is_read'])): ?>
                            <form method="post" action="<?= $escape($baseUrl) ?>/admin/notifications/read">
                                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                                <input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>">
                                <button class="btn btn-sm btn-outline-coffee" type="submit">Mark as read</button>
                            </form>
                        <?php else: ?>
                            <span class="badge text-bg-light">Read</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/admin_layout.php';
