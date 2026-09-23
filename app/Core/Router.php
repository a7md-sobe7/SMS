<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ModelNotFoundException;
use App\Middleware\MiddlewareInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * RESTful Regex Routing Engine with Dependency Injection & FormRequest Auto-wiring
 */
class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private static ?self $instance = null;
    private Container $container;

    public function __construct(?Container $container = null)
    {
        self::$instance = $this;
        $this->container = $container ?? Container::getInstance();
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
                $routeParams = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $routeParams[$key] = $value;
                    }
                }
                $request->setRouteParams($routeParams);

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
                            $fullMwClass = "\\App\\Middleware\\" . $mwClass;
                            if (class_exists($fullMwClass)) {
                                $mwClass = $fullMwClass;
                            } else {
                                throw new RuntimeException("Middleware class not found: {$mwClass}");
                            }
                        }
                        $instance = $this->container->make($mwClass);
                    } else {
                        $instance = $mwClass;
                    }

                    if ($instance instanceof MiddlewareInterface) {
                        $result = $instance->handle($request, $mwArgs);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }

                // 2. Execute Action with Dependency Injection
                $action = $route['action'];

                if (is_callable($action)) {
                    return $this->container->call($action, ['request' => $request]);
                }

                if (is_array($action) && count($action) === 2) {
                    [$controllerClass, $method] = $action;

                    if (!class_exists($controllerClass)) {
                        throw new RuntimeException("Controller class not found: {$controllerClass}");
                    }

                    $controller = $this->container->make($controllerClass);
                    if (!method_exists($controller, $method)) {
                        throw new RuntimeException("Method '{$method}' not found on controller '{$controllerClass}'");
                    }

                    // Resolve method parameters (FormRequests, Request, Route Params, Services)
                    $refMethod = new ReflectionMethod($controller, $method);
                    $args = [];

                    foreach ($refMethod->getParameters() as $param) {
                        $paramName = $param->getName();
                        $paramType = $param->getType();

                        if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                            $className = $paramType->getName();

                            // Auto-wire FormRequest with automatic validation & authorization!
                            if (is_subclass_of($className, FormRequest::class)) {
                                $args[] = $className::createFromRequest($request);
                                continue;
                            }

                            // Inject Request instance
                            if ($className === Request::class || is_subclass_of($className, Request::class)) {
                                $args[] = $request;
                                continue;
                            }

                            // Resolve domain dependency from Container
                            $args[] = $this->container->make($className);
                            continue;
                        }

                        // Check if named route parameter exists
                        if (array_key_exists($paramName, $routeParams)) {
                            $args[] = $routeParams[$paramName];
                        } elseif ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } elseif ($param->allowsNull()) {
                            $args[] = null;
                        } else {
                            $args[] = null;
                        }
                    }

                    return $refMethod->invokeArgs($controller, $args);
                }

                throw new RuntimeException("Invalid route action defined for: {$requestPath}");
            }
        }

        // Throw Model/Route Not Found Exception
        throw new ModelNotFoundException('Route', [], "Endpoint not found: [{$requestMethod}] {$requestPath}");
    }
}
