<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

/**
 * PSR-11 Compatible Dependency Injection / IoC Service Container
 * 
 * Supports constructor auto-wiring, method injection, interface binding,
 * contextual singletons, and lifecycle resolution.
 */
class Container
{
    private static ?self $instance = null;

    /**
     * The container's registered bindings.
     * @var array<string, array{concrete: mixed, shared: bool}>
     */
    private array $bindings = [];

    /**
     * The container's shared singleton instances.
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Registered service aliases.
     * @var array<string, string>
     */
    private array $aliases = [];

    public function __construct()
    {
        self::$instance = $this;
        $this->instance(self::class, $this);
    }

    /**
     * Get the globally available container instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set the globally available container instance.
     */
    public static function setInstance(?self $container): ?self
    {
        return self::$instance = $container;
    }

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $concrete = $concrete ?? $abstract;

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => $shared
        ];
    }

    /**
     * Register a shared singleton binding with the container.
     */
    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->instances[$abstract] = $instance;
        return $instance;
    }

    /**
     * Register an alias for an abstract binding.
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Check if a type or identifier is bound in the container.
     */
    public function has(string $id): bool
    {
        $abstract = $this->getAlias($id);
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || class_exists($abstract);
    }

    /**
     * Resolve the given type from the container.
     */
    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    /**
     * Resolve the given type from the container with optional parameters.
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->getAlias($abstract);

        // If an instance is already shared, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        // If the concrete is buildable or is a Closure
        if ($concrete instanceof Closure) {
            $object = $concrete($this, $parameters);
        } elseif (is_string($concrete) && ($concrete === $abstract || class_exists($concrete))) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $concrete;
        }

        // Cache singleton instances
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Instantiate a concrete instance of a given class with recursive dependency injection.
     */
    public function build(string $concrete, array $parameters = []): mixed
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new RuntimeException("Target class [{$concrete}] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Target [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If no constructor exists, simply instantiate
        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveParameters($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Call the given callable or [Class, Method] array, auto-injecting its dependencies.
     */
    public function call(callable|array $callable, array $parameters = []): mixed
    {
        if (is_array($callable)) {
            [$class, $method] = $callable;
            $instance = is_object($class) ? $class : $this->make($class);
            $reflector = new ReflectionMethod($instance, $method);
            $dependencies = $this->resolveParameters($reflector->getParameters(), $parameters);
            return $reflector->invokeArgs($instance, $dependencies);
        }

        if ($callable instanceof Closure || is_string($callable)) {
            $reflector = new ReflectionFunction($callable);
            $dependencies = $this->resolveParameters($reflector->getParameters(), $parameters);
            return $reflector->invokeArgs($dependencies);
        }

        return call_user_func_array($callable, $parameters);
    }

    /**
     * Resolve reflection parameters for a constructor or method.
     *
     * @param ReflectionParameter[] $parameters
     * @param array $providedParameters
     * @return array
     */
    private function resolveParameters(array $parameters, array $providedParameters = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // 1. Check if explicitly provided by name
            if (array_key_exists($name, $providedParameters)) {
                $dependencies[] = $providedParameters[$name];
                continue;
            }

            $type = $parameter->getType();

            // 2. If no type hint exists, resolve default or error
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new RuntimeException("Unresolvable parameter [{$name}] in class {$parameter->getDeclaringClass()?->getName()}");
                }
                continue;
            }

            // 3. Resolve class or interface dependency via Container
            $className = $type->getName();

            // Check if explicitly provided by class name
            if (array_key_exists($className, $providedParameters)) {
                $dependencies[] = $providedParameters[$className];
                continue;
            }

            try {
                $dependencies[] = $this->make($className);
            } catch (\Throwable $e) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new RuntimeException("Unable to resolve dependency [{$className}] for parameter [{$name}]: " . $e->getMessage(), 0, $e);
                }
            }
        }

        return $dependencies;
    }

    /**
     * Get the concrete type for a given abstract.
     */
    private function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Get the aliased name if set.
     */
    private function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }
}
