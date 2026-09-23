<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;
use PDO;

/**
 * Centralized Input Validation Engine
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }

    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleSet) {
            $fieldRules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $label = ucwords(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "The {$label} field is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$label} must be a valid email address.");
                }
                break;

            case 'min':
                $min = (int)($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) < $min) {
                    $this->addError($field, "The {$label} must be at least {$min} characters.");
                } elseif (is_numeric($value) && (float)$value < $min) {
                    $this->addError($field, "The {$label} must be at least {$min}.");
                }
                break;

            case 'max':
                $max = (int)($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) > $max) {
                    $this->addError($field, "The {$label} may not exceed {$max} characters.");
                } elseif (is_numeric($value) && (float)$value > $max) {
                    $this->addError($field, "The {$label} may not exceed {$max}.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "The {$label} must be a valid number.");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array((string)$value, $params, true)) {
                    $allowed = implode(', ', $params);
                    $this->addError($field, "The selected {$label} is invalid. Allowed values: {$allowed}.");
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $d = \DateTime::createFromFormat('Y-m-d', (string)$value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, "The {$label} is not a valid date (YYYY-MM-DD).");
                    }
                }
                break;

            case 'unique':
                // format: unique:table,column[,ignoreId,ignoreColumn]
                if (!empty($value) && isset($params[0], $params[1])) {
                    $table = $params[0];
                    $column = $params[1];
                    $ignoreId = $params[2] ?? null;
                    $ignoreColumn = $params[3] ?? 'id';

                    $pdo = Database::getConnection();
                    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                    $queryParams = ['val' => $value];

                    if ($ignoreId !== null && $ignoreId !== '') {
                        $sql .= " AND `{$ignoreColumn}` != :ignore_id";
                        $queryParams['ignore_id'] = $ignoreId;
                    }

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($queryParams);
                    if ((int)$stmt->fetchColumn() > 0) {
                        $this->addError($field, "The {$label} has already been taken.");
                    }
                }
                break;

            case 'same':
                $targetField = $params[0] ?? '';
                $targetValue = $this->data[$targetField] ?? null;
                if ($value !== $targetValue) {
                    $targetLabel = ucwords(str_replace('_', ' ', $targetField));
                    $this->addError($field, "The {$label} and {$targetLabel} must match.");
                }
                break;
        }
    }

    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
