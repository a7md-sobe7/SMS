<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use RuntimeException;

/**
 * RESTful Regex Routing Engine with Parameter Extraction and Middleware Pipelines
 */
class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private array $namedRoutes = [];
    private static ?self $instance = null;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->add('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->add('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->add('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->add('DELETE', $uri, $action, $middleware);
    }

    public function add(string $method, string $uri, array|callable $action, array $middleware = []): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, (array)$group['middleware']);
            }
        }

        $fullUri = rtrim($prefix . '/' . trim($uri, '/'), '/') ?: '/';
        $allMiddleware = array_merge($groupMiddleware, $middleware);

        // Convert {param} placeholders to regex capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullUri);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => strtoupper($method),
            'uri'        => $fullUri,
            'regex'      => $regex,
            'action'     => $action,
            'middleware' => $allMiddleware
        ];

        return $this;
    }

    /**
     * Group routes with a shared URI prefix and/or middleware stack.
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Dispatch the current request through matched route middleware and controller action.
     */
    public function dispatch(Request $request): mixed
    {
        $requestMethod = $request->getMethod();
        $requestPath = rtrim($request->getPath(), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['regex'], $requestPath, $matches)) {
                // Extract named route parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $request->setRouteParams($params);

                // 1. Run Middleware Pipeline
                foreach ($route['middleware'] as $mw) {
                    $mwClass = $mw;
                    $mwArgs = [];

                    if (is_string($mw) && str_contains($mw, ':')) {
                        [$mwClass, $argsString] = explode(':', $mw, 2);
                        $mwArgs = explode(',', $argsString);
                    }

                    if (is_string($mwClass)) {
                        if (!class_exists($mwClass)) {
                            // Try App\Middleware namespace prefix
                            $fullMwClass = "\\App\\Middleware\\" . $mwClass;
                            if (class_exists($fullMwClass)) {
                                $mwClass = $fullMwClass;
                            } else {
                                throw new RuntimeException("Middleware class not found: {$mwClass}");
                            }
                        }
                        $instance = new $mwClass();
                    } else {
                        $instance = $mwClass;
                    }

                    if ($instance instanceof MiddlewareInterface) {
                        $result = $instance->handle($request, $mwArgs);
                        if ($result !== null) {
                            return $result; // Middleware halted execution (e.g. redirected or returned JSON error)
                        }
                    }
                }

                // 2. Execute Action
                $action = $route['action'];

                if (is_callable($action)) {
                    return call_user_func($action, $request);
                }

                if (is_array($action) && count($action) === 2) {
                    [$controllerClass, $method] = $action;

                    if (!class_exists($controllerClass)) {
                        throw new RuntimeException("Controller class not found: {$controllerClass}");
                    }

                    $controller = new $controllerClass();
                    if (!method_exists($controller, $method)) {
                        throw new RuntimeException("Method '{$method}' not found on controller '{$controllerClass}'");
                    }

                    return $controller->$method($request);
                }

                throw new RuntimeException("Invalid route action defined for: {$requestPath}");
            }
        }

        // 404 Not Found Handler
        if ($request->isAjax() || str_starts_with($requestPath, '/api/')) {
            json_response(null, 404, "Endpoint not found: [{$requestMethod}] {$requestPath}");
        }

        http_response_code(404);
        view('errors/404', ['path' => $requestPath], 'main');
        return null;
    }
}
