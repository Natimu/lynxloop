<?php


class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][trim($path, '/')] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][trim($path, '/')] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $path = trim($path, '/');

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo "404 - Page not found";
            return;
        }

        [$controller, $action] = explode('@', $this->routes[$method][$path]);

        if (!class_exists($controller)) {
            http_response_code(500);
            echo "Controller not found";
            return;
        }

        $instance = new $controller();

        if (!method_exists($instance, $action)) {
            http_response_code(500);
            echo "Method not found";
            return;
        }

        $instance->$action();
    }
}