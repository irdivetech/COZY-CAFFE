<?php

class NotificationHelper
{
    public static function notifyUser(int $userId, string $title, string $message, ?string $link = null): void
    {
        $statement = Database::getInstance()->prepare(
            'INSERT INTO notifications (user_id, title, message, link)
             VALUES (:user_id, :title, :message, :link)'
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }

    public static function notifyAdmins(string $title, string $message, ?string $link = null): void
    {
        $statement = Database::getInstance()->prepare(
            "INSERT INTO notifications (user_id, title, message, link)
             SELECT id, :title, :message, :link
             FROM users
             WHERE role = 'admin'"
        );
        $statement->execute([
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }

    public static function forUser(int $userId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 100));
        $statement = Database::getInstance()->prepare(
            'SELECT id, title, message, is_read, link, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT ' . $limit
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $statement = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public static function markRead(int $userId, int $notificationId): bool
    {
        $statement = Database::getInstance()->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id AND user_id = :user_id AND is_read = 0'
        );
        $statement->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public static function markAllRead(int $userId): int
    {
        $statement = Database::getInstance()->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id AND is_read = 0'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->rowCount();
    }

    public static function internalLink(?string $link, string $baseUrl, string $fallback): string
    {
        if ($link === null || !str_starts_with($link, '/') || str_starts_with($link, '//')) {
            return $baseUrl . $fallback;
        }

        return $baseUrl . $link;
    }
}
