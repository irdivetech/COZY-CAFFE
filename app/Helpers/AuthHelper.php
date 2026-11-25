<?php

class AuthHelper
{
    public static function requireLogin(): void
    {
        if (!self::currentUser()) {
            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $baseUrl = dirname($scriptName);
            $baseUrl = $baseUrl === '/' || $baseUrl === '.' ? '' : rtrim($baseUrl, '/');
            header('Location: ' . $baseUrl . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo '403 - Forbidden';
            exit;
        }
    }

    public static function requireCustomer(): void
    {
        self::requireLogin();

        if (($_SESSION['user']['role'] ?? '') !== 'customer') {
            http_response_code(403);
            echo '403 - Forbidden';
            exit;
        }
    }

    public static function currentUser(): ?array
    {
        return isset($_SESSION['user']) && is_array($_SESSION['user'])
            ? $_SESSION['user']
            : null;
    }
}
