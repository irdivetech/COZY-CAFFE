<?php

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath === '/') {
            $normalizedPath = '/';
        }

        $this->routes[$method][$normalizedPath] = $handler;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $scriptDirectory = rtrim($scriptDirectory, '/');

        if ($scriptDirectory !== '' && $scriptDirectory !== '/' && str_starts_with($path, $scriptDirectory)) {
            $path = substr($path, strlen($scriptDirectory));
        }

        $path = '/' . trim($path, '/');

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo '404 - Halaman tidak ditemukan.';
            return;
        }

        [$controllerClass, $methodName] = $this->routes[$method][$path];

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo '500 - Controller tidak ditemukan.';
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            echo '500 - Method controller tidak ditemukan.';
            return;
        }

        call_user_func([$controller, $methodName]);
    }
}
