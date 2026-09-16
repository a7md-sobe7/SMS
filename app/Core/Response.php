<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response Dispatcher
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function make(string $content = '', int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }

    public static function json(mixed $data = null, int $statusCode = 200, string $message = '', array $errors = []): self
    {
        $isSuccess = $statusCode >= 200 && $statusCode < 300;

        $payload = [
            'success'   => $isSuccess,
            'message'   => $message,
            'data'      => $data,
            'errors'    => !empty($errors) ? $errors : null,
            'meta'      => [
                'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'code'      => $statusCode
            ]
        ];

        $content = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return new self($content, $statusCode, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send(): void
    {
        if (headers_sent()) {
            echo $this->content;
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
        exit;
    }
}
