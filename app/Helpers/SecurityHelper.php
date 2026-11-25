<?php

class SecurityHelper
{
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrfToken(?string $token = null): bool
    {
        $submittedToken = $token ?? ($_POST['_csrf_token'] ?? '');
        $sessionToken = $_SESSION['_csrf_token'] ?? '';

        return is_string($submittedToken)
            && is_string($sessionToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $submittedToken);
    }

    public static function e(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function validateImageUpload(array $file): bool
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 2 * 1024 * 1024) {
            return false;
        }

        $temporaryPath = $file['tmp_name'] ?? '';
        if (!is_string($temporaryPath) || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            return false;
        }

        $imageInfo = @getimagesize($temporaryPath);
        if ($imageInfo === false) {
            return false;
        }

        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
        return in_array($imageInfo[2] ?? null, $allowedTypes, true);
    }
}
