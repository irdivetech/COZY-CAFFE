<?php

class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = dirname(__DIR__, 2) . '/views/' . ltrim($viewPath, '/') . '.php';

        if (!is_file($file)) {
            http_response_code(500);
            echo '500 - View not found';
            return;
        }

        require $file;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function appBaseUrl(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseUrl = dirname($scriptName);

        return $baseUrl === '/' || $baseUrl === '.' ? '' : rtrim($baseUrl, '/');
    }
}
