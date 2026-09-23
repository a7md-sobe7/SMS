<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AuthorizationException;
use App\Exceptions\ValidationException;

/**
 * Base Form Request Class
 * 
 * Encapsulates input validation rules, custom authorization checks,
 * sanitized data extraction, and DTO conversion per endpoint action.
 */
abstract class FormRequest extends Request
{
    private array $validatedData = [];

    public function __construct(
        string $method = 'GET',
        string $uri = '/',
        array $queryParams = [],
        array $bodyParams = [],
        array $files = [],
        array $headers = []
    ) {
        parent::__construct(
            $method,
            $uri,
            $queryParams,
            $bodyParams,
            $files,
            $headers
        );
    }

    /**
     * Create a FormRequest instance from the active captured Request.
     */
    public static function createFromRequest(Request $request): static
    {
        $instance = new static(
            $request->getMethod(),
            $request->getUri(),
            $request->getQueryParams(),
            $request->getBodyParams(),
            $request->getFiles(),
            $request->getHeaders()
        );

        $instance->setRouteParams($request->getRouteParams());
        $instance->validateResolved();

        return $instance;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array<string, string>
     */
    abstract public function rules(): array;

    /**
     * Get custom error messages for validator errors.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Run the authorization and validation pipeline.
     */
    public function validateResolved(): void
    {
        // 1. Check Authorization
        if (!$this->authorize()) {
            throw new AuthorizationException("This action is unauthorized by request policy.");
        }

        // 2. Check Validation Rules
        $rules = $this->rules();
        if (!empty($rules)) {
            $validator = Validator::make($this->all(), $rules, $this->messages());

            if ($validator->fails()) {
                throw new ValidationException(
                    $validator->errors(),
                    'The given data failed validation.'
                );
            }
        }

        // 3. Extract Validated Data
        $input = $this->all();
        $this->validatedData = [];

        if (empty($rules)) {
            $this->validatedData = $input;
        } else {
            foreach (array_keys($rules) as $field) {
                $baseField = explode('.', $field)[0];
                if (array_key_exists($baseField, $input)) {
                    $this->validatedData[$baseField] = $input[$baseField];
                }
            }
        }
    }

    /**
     * Get the validated input data.
     */
    public function validated(): array
    {
        return !empty($this->validatedData) ? $this->validatedData : $this->all();
    }
}
