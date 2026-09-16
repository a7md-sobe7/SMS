<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Repositories\AuditLogRepository;

class AttendanceController extends Controller
{
    private AttendanceRepository $attendanceRepo;
    private CourseRepository $courseRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->attendanceRepo = new AttendanceRepository();
        $this->courseRepo = new CourseRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $date     = (string)$request->input('date', date('Y-m-d'));

        $courses = $this->courseRepo->findAllWithDetails();
        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = $this->courseRepo->findWithDetails($courseId);
            $roster = $this->attendanceRepo->getCourseAttendanceByDate($courseId, $date);
        }

        $this->render('attendance/index', [
            'pageTitle'      => 'Daily Course Attendance Tracking',
            'courses'        => $courses,
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId,
            'currentDate'    => $date
        ]);
    }

    public function record(Request $request): void
    {
        $courseId = (int)$request->input('course_id');
        $date     = (string)$request->input('date', date('Y-m-d'));
        $records  = (array)$request->input('attendance', []);

        if (!$courseId || empty($records)) {
            $this->redirectWith('/attendance', 'error', 'No attendance data provided.');
        }

        foreach ($records as $studentId => $status) {
            $this->attendanceRepo->upsert(
                (int)$studentId,
                $courseId,
                $date,
                (string)$status
            );
        }

        $this->auditRepo->log(
            auth_id(),
            'ATTENDANCE_RECORDED',
            'Attendance',
            null,
            ['course_id' => $courseId, 'date' => $date, 'count' => count($records)]
        );

        $this->redirectWith("/attendance?course_id={$courseId}&date={$date}", 'success', 'Attendance roll-call saved successfully!');
    }
}
