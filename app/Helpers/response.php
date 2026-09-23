<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Events\EventDispatcher;
use App\Core\Gate;

if (!function_exists('app')) {
    /**
     * Get the available container instance or resolve a binding.
     */
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $container = Container::getInstance();
        if ($abstract === null) {
            return $container;
        }
        return $container->make($abstract, $parameters);
    }
}

if (!function_exists('event')) {
    /**
     * Dispatch an event and call the registered listeners.
     */
    function event(object $event): array
    {
        return EventDispatcher::getInstance()->dispatch($event);
    }
}

if (!function_exists('gate')) {
    /**
     * Retrieve the Gate authorization manager.
     */
    function gate(): Gate
    {
        return Gate::getInstance();
    }
}

if (!function_exists('json_response')) {
    /**
     * Send a standardized JSON response and terminate script execution.
     */
    function json_response(mixed $data = null, int $statusCode = 200, string $message = '', array $errors = [], array $meta = []): void
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $isSuccess = $statusCode >= 200 && $statusCode < 300;

        $response = [
            'success'   => $isSuccess,
            'message'   => $message,
            'data'      => $data,
            'errors'    => !empty($errors) ? $errors : null,
            'meta'      => array_merge([
                'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'code'      => $statusCode
            ], $meta)
        ];

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect the user to a given URL with an optional flash status message.
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

if (!function_exists('view')) {
    /**
     * Render a server-side PHP view template wrapped inside a master layout.
     */
    function view(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        $normalizedPath = str_replace('.', '/', $viewPath);
        $fullPath = dirname(__DIR__, 2) . "/views/{$normalizedPath}.php";

        if (!file_exists($fullPath)) {
            http_response_code(500);
            throw new RuntimeException("View template not found at: {$fullPath}");
        }

        // Extract variables into local scope safely
        extract($data, EXTR_SKIP);

        if ($layout === null) {
            require $fullPath;
            return;
        }

        $layoutPath = dirname(__DIR__, 2) . "/views/layouts/{$layout}.php";
        if (!file_exists($layoutPath)) {
            throw new RuntimeException("Layout template not found at: {$layoutPath}");
        }

        // Capture view content into $content variable for layout insertion
        ob_start();
        require $fullPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}
