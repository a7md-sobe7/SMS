<?php

declare(strict_types=1);

if (!function_exists('json_response')) {
    /**
     * Send a standardized JSON response and terminate script execution.
     * 
     * @param mixed $data Payload to return under 'data' key or custom object.
     * @param int $statusCode HTTP status code (default 200).
     * @param string $message User/developer feedback message.
     * @param array $errors Validation or error dictionary.
     */
    function json_response(mixed $data = null, int $statusCode = 200, string $message = '', array $errors = []): void
    {
        // Clear any previous output buffers to guarantee clean JSON
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
            'meta'      => [
                'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'code'      => $statusCode
            ]
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
     * 
     * @param string $viewPath Dot-notated or slash path (e.g. 'students.index' or 'students/index').
     * @param array $data Variables to extract into the view's scope.
     * @param string|null $layout Layout file inside views/layouts/ or null for no layout.
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
