<?php

declare(strict_types=1);

namespace App\Core\Events;

use App\Core\Container;

/**
 * Event Dispatcher Subsystem
 * 
 * Allows decoupled event publishing and synchronous/asynchronous listener triggering.
 */
class EventDispatcher
{
    private static ?self $instance = null;

    /**
     * Registered event listeners map: [EventClass => [Listener1, Listener2, ...]]
     * @var array<string, array<int, string|callable>>
     */
    private array $listeners = [];

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
     * Register an event listener for a given event class.
     */
    public function listen(string $eventClass, string|callable $listener): self
    {
        $this->listeners[$eventClass][] = $listener;
        return $this;
    }

    /**
     * Dispatch an event to all its registered listeners.
     */
    public function dispatch(object $event): array
    {
        $eventClass = get_class($event);
        $responses = [];

        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                if (is_callable($listener)) {
                    $responses[] = call_user_func($listener, $event);
                } elseif (is_string($listener) && class_exists($listener)) {
                    $listenerInstance = Container::getInstance()->make($listener);
                    if (method_exists($listenerInstance, 'handle')) {
                        $responses[] = $listenerInstance->handle($event);
                    }
                }
            }
        }

        return $responses;
    }
}
