<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;
use App\Core\Response;

/**
 * Base API JSON Resource Transformer
 * 
 * Provides consistent REST API presentation transformations:
 * { "success": true, "message": "...", "data": { ... }, "meta": { ... } }
 */
abstract class JsonResource
{
    public mixed $resource;

    public function __construct(mixed $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     */
    abstract public function toArray(Request $request): array;

    /**
     * Transform a single item into transformed payload.
     */
    public static function make(mixed $resource): static
    {
        return new static($resource);
    }

    /**
     * Transform a collection of items.
     */
    public static function collection(array $resources, ?Request $request = null): array
    {
        $request = $request ?? Request::capture();
        return array_map(function ($item) use ($request) {
            $resource = new static($item);
            return $resource->toArray($request);
        }, $resources);
    }

    /**
     * Format as a standard Response object.
     */
    public function toResponse(?Request $request = null, int $status = 200, string $message = 'Success', array $meta = []): Response
    {
        $request = $request ?? Request::capture();
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $this->toArray($request)
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return Response::rawJson($payload, $status);
    }

    /**
     * Helper to wrap raw array or collection with standard JSON response.
     */
    public static function successResponse(mixed $data, string $message = 'Success', int $status = 200, array $meta = []): Response
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return Response::rawJson($payload, $status);
    }
}
