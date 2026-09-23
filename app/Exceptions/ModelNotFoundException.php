<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an entity model record cannot be found in the database (404 Not Found).
 */
class ModelNotFoundException extends Exception
{
    private string $model;
    private array $ids;

    public function __construct(string $model = 'Record', array $ids = [], string $message = '', int $code = 404)
    {
        $this->model = $model;
        $this->ids = $ids;
        $idList = !empty($ids) ? ' [' . implode(', ', $ids) . ']' : '';
        $msg = $message !== '' ? $message : "No query results for model [{$model}]{$idList}.";
        parent::__construct($msg, $code);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
