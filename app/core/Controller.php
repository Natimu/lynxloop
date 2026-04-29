<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $contentView = __DIR__ . '/../views/' . $view . '.php';
        $layoutView = __DIR__ . '/../views/layouts/main.php';

        if (!file_exists($contentView)) {
            http_response_code(500);
            echo "<h1>View not found: {$view}</h1>";
            return;
        }

        if (!file_exists($layoutView)) {
            http_response_code(500);
            echo "<h1>Layout not found</h1>";
            return;
        }

        require $layoutView;
    }

    /**
     * Render a view without the main layout wrapper.
     */
    protected function viewRaw(string $view, array $data = []): void
    {
        extract($data);

        $contentView = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($contentView)) {
            http_response_code(500);
            echo "<h1>View not found: {$view}</h1>";
            return;
        }

        require $contentView;
    }

     protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}