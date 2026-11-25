<?php

class FlashHelper
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function get(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($messages) ? $messages : [];
    }
}
