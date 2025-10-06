<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[$method][$normalizedPath] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $normalizedPath = $this->normalizePath($uri);
        $handler = $this->routes[$method][$normalizedPath] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '<h1>404 Not Found</h1>';
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $methodName] = $handler;
            $instance = new $class();
            $instance->$methodName();
            return;
        }

        $handler();
    }

    private function normalizePath(string $path): string
    {
        $parsed = parse_url($path, PHP_URL_PATH) ?? '/';
        $normalized = '/' . trim($parsed, '/');

        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized === '' ? '/' : $normalized;
    }
}
