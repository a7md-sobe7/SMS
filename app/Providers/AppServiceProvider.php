<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Events\EventDispatcher;
use App\Core\Exceptions\Handler;
use App\Core\Gate;
use App\Core\Queue\QueueManager;
use App\Core\Queue\QueueWorker;
use App\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Core Singletons
        $this->app->singleton(EventDispatcher::class, fn() => EventDispatcher::getInstance());
        $this->app->singleton(Gate::class, fn() => Gate::getInstance());
        $this->app->singleton(QueueManager::class, fn() => QueueManager::getInstance());
        $this->app->singleton(QueueWorker::class, fn($app) => new QueueWorker($app->make(QueueManager::class)));
        $this->app->singleton(Handler::class, fn() => new Handler());
    }

    public function boot(): void
    {
        // Core application bootstrapping
    }
}
