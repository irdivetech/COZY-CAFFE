<?php
$pageTitle = $pageTitle ?? 'Notifications';
$notifications = $notifications ?? [];
$escape = static fn (?string $value): string =>
    htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$baseUrl = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($baseUrl === '/') {
    $baseUrl = '';
}
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-muted mb-1">Updates from CozyCafe</p>
            <h1 class="section-title h2 mb-0">Notifications</h1>
        </div>
        <?php if (array_filter($notifications, static fn (array $item): bool => empty($item['is_read']))): ?>
            <form method="post" action="<?= $escape($baseUrl) ?>/notifications/read-all">
                <input type="hidden" name="_csrf_token" value="<?= $escape(SecurityHelper::generateCsrfToken()) ?>">
                <button class="btn btn-outline-coffee" type="submit">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if (!$notifications): ?>
        <div class="cozy-card p-5 text-center"><h2 class="h5">You're all caught up</h2><p class="text-muted mb-0">There are no notifications yet.</p></div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($notifications as $notification): ?>
                <article class="cozy-card p-4 <?= empty($notification['is_read']) ? 'notification-unread' : '' ?>">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h5 mb-1"><?= $escape($notification['title']) ?></h2>
                            <p class="mb-1"><?= nl2br($escape($notification['message'])) ?></p>
                            <small class="text-muted"><?= $escape($notification['created_at']) ?></small>
                            <div><a href="<?= $escape(NotificationHelper::internalLink($notification['link'], $baseUrl, '/orders')) ?>">Open related page</a></div>
                        </div>
                        <?php if (empty($notification['is_read'])): ?>
                            <form method="post" action="<?= $escape($baseUrl) ?>/notifications/read">
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
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/customer_layout.php';
