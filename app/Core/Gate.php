<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AuthorizationException;

/**
 * Authorization Gate & Policy Engine
 * 
 * Manages policy mappings and evaluates user permissions across resources.
 */
class Gate
{
    private static ?self $instance = null;

    /**
     * Map of models / entity types to policy class names.
     * @var array<string, string>
     */
    private array $policies = [];

    /**
     * Named ability callback closures.
     * @var array<string, callable>
     */
    private array $abilities = [];

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

    /**
     * Register a policy class for a given model or entity key.
     */
    public function policy(string $classOrKey, string $policyClass): self
    {
        $this->policies[$classOrKey] = $policyClass;
        return $this;
    }

    /**
     * Define a standalone authorization ability.
     */
    public function define(string $ability, callable $callback): self
    {
        $this->abilities[$ability] = $callback;
        return $this;
    }

    /**
     * Determine if the current authenticated user has the given ability.
     */
    public function allows(string $ability, mixed $arguments = null, ?array $user = null): bool
    {
        $user = $user ?? (function_exists('auth_user') ? auth_user() : null);

        // 1. Check direct ability definitions
        if (isset($this->abilities[$ability])) {
            return (bool)call_user_func($this->abilities[$ability], $user, $arguments);
        }

        // 2. Check model/policy mappings
        $targetClass = is_object($arguments) ? get_class($arguments) : (is_string($arguments) ? $arguments : null);

        if ($targetClass !== null && isset($this->policies[$targetClass])) {
            $policyClass = $this->policies[$targetClass];
            $policy = Container::getInstance()->make($policyClass);

            if (method_exists($policy, $ability)) {
                return (bool)$policy->$ability($user, $arguments);
            }
        }

        // 3. Fallback: Search all registered policies for the method
        foreach ($this->policies as $policyClass) {
            if (class_exists($policyClass)) {
                $policy = Container::getInstance()->make($policyClass);
                if (method_exists($policy, $ability)) {
                    return (bool)$policy->$ability($user, $arguments);
                }
            }
        }

        return false;
    }

    /**
     * Determine if the current user is denied the given ability.
     */
    public function denies(string $ability, mixed $arguments = null, ?array $user = null): bool
    {
        return !$this->allows($ability, $arguments, $user);
    }

    /**
     * Authorize an ability or throw an AuthorizationException (403).
     */
    public function authorize(string $ability, mixed $arguments = null, ?array $user = null): void
    {
        if ($this->denies($ability, $arguments, $user)) {
            throw new AuthorizationException("Unauthorized: Access denied for action [{$ability}].");
        }
    }
}
