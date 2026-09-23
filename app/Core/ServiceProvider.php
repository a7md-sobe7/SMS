<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Abstract Base Service Provider
 * 
 * Defines the contract for bootstrapping services, event listeners,
 * repository interface bindings, and policies.
 */
abstract class ServiceProvider
{
    protected Container $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Register services, singletons, and interface bindings into the container.
     */
    abstract public function register(): void;

    /**
     * Bootstrap any application services, event listeners, or policies.
     */
    public function boot(): void
    {
        // Optional hook for subclasses
    }
}
