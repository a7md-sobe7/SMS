<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render a server-side view template.
     */
    protected function render(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        view($viewPath, $data, $layout);
    }

    /**
     * Return a standardized JSON response.
     */
    protected function json(mixed $data = null, int $statusCode = 200, string $message = '', array $errors = []): void
    {
        json_response($data, $statusCode, $message, $errors);
    }

    /**
     * Redirect with optional session flash message.
     */
    protected function redirectWith(string $url, string $flashType, string $flashMessage): void
    {
        Session::flash($flashType, $flashMessage);
        redirect($url);
    }
}
