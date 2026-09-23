<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Events\EventDispatcher;
use App\Core\ServiceProvider;
use App\Events\AttendanceRecordedEvent;
use App\Events\CourseCapacityReachedEvent;
use App\Events\GradeAssignedEvent;
use App\Events\StudentArchivedEvent;
use App\Events\StudentEnrolledEvent;
use App\Events\StudentRegisteredEvent;
use App\Events\StudentUpdatedEvent;
use App\Events\UserLoggedInEvent;
use App\Listeners\LogAuditTrailListener;
use App\Listeners\NotifyAdvisorListener;
use App\Listeners\RecalculateGpaTranscriptListener;
use App\Listeners\SendWelcomeNotificationListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Map of events to their registered listeners.
     * @var array<string, array<int, string>>
     */
    protected array $listen = [
        UserLoggedInEvent::class => [
            LogAuditTrailListener::class,
        ],
        StudentRegisteredEvent::class => [
            LogAuditTrailListener::class,
            SendWelcomeNotificationListener::class,
        ],
        StudentUpdatedEvent::class => [
            LogAuditTrailListener::class,
        ],
        StudentArchivedEvent::class => [
            LogAuditTrailListener::class,
        ],
        GradeAssignedEvent::class => [
            LogAuditTrailListener::class,
            RecalculateGpaTranscriptListener::class,
        ],
        AttendanceRecordedEvent::class => [
            LogAuditTrailListener::class,
        ],
        StudentEnrolledEvent::class => [
            LogAuditTrailListener::class,
        ],
        CourseCapacityReachedEvent::class => [
            NotifyAdvisorListener::class,
        ],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $dispatcher = $this->app->make(EventDispatcher::class);

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
}
