<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Core\Queue\QueueManager;
use App\Events\AttendanceRecordedEvent;
use App\Events\GradeAssignedEvent;
use App\Events\StudentArchivedEvent;
use App\Events\StudentEnrolledEvent;
use App\Events\StudentRegisteredEvent;
use App\Events\StudentUpdatedEvent;
use App\Events\UserLoggedInEvent;
use App\Jobs\ProcessAuditLogJob;

class LogAuditTrailListener
{
    private QueueManager $queue;

    public function __construct(QueueManager $queue)
    {
        $this->queue = $queue;
    }

    public function handle(object $event): void
    {
        $job = null;

        if ($event instanceof UserLoggedInEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->userId,
                action: 'USER_LOGIN',
                entityType: 'User',
                entityId: $event->userId,
                details: ['username' => $event->username, 'role' => $event->role],
                ipAddress: $event->ipAddress
            );
        } elseif ($event instanceof StudentRegisteredEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'STUDENT_CREATED',
                entityType: 'Student',
                entityId: $event->studentId,
                details: ['student_code' => $event->studentCode, 'name' => $event->name]
            );
        } elseif ($event instanceof StudentUpdatedEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'STUDENT_UPDATED',
                entityType: 'Student',
                entityId: $event->studentId,
                details: array_merge(['student_code' => $event->studentCode], $event->changes)
            );
        } elseif ($event instanceof StudentArchivedEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'STUDENT_ARCHIVED',
                entityType: 'Student',
                entityId: $event->studentId,
                details: ['student_code' => $event->studentCode]
            );
        } elseif ($event instanceof GradeAssignedEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'GRADE_ASSIGNED',
                entityType: 'Grade',
                entityId: $event->gradeId,
                details: ['enrollment_id' => $event->enrollmentId, 'letter' => $event->letterGrade, 'total' => $event->totalGrade]
            );
        } elseif ($event instanceof AttendanceRecordedEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'ATTENDANCE_RECORDED',
                entityType: 'Attendance',
                entityId: $event->studentId,
                details: ['course_id' => $event->courseId, 'date' => $event->date, 'status' => $event->status]
            );
        } elseif ($event instanceof StudentEnrolledEvent) {
            $job = new ProcessAuditLogJob(
                userId: $event->performedByUserId,
                action: 'STUDENT_ENROLLED',
                entityType: 'Enrollment',
                entityId: $event->enrollmentId,
                details: ['student_id' => $event->studentId, 'course_id' => $event->courseId]
            );
        }

        if ($job !== null) {
            // Push to background queue and execute immediately or through worker
            $this->queue->push($job);
            // Execute for instant audit trail persistence
            $job->handle();
        }
    }
}
