<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\DTOs\Attendance\AttendanceDTO;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'attendance');

        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $date     = (string)$request->input('date', date('Y-m-d'));

        $overview = $this->attendanceService->getAttendanceView($courseId, $date);

        $this->render('attendance/index', [
            'pageTitle'      => 'Daily Course Attendance Tracking',
            'courses'        => $overview['courses'],
            'selectedCourse' => $overview['selectedCourse'],
            'roster'         => $overview['roster'],
            'courseId'       => $courseId,
            'currentDate'    => $date
        ]);
    }

    public function record(Request $request): void
    {
        $this->gate->authorize('record', 'attendance');

        $courseId = (int)$request->input('course_id');
        $date     = (string)$request->input('date', date('Y-m-d'));
        $records  = (array)$request->input('attendance', []);

        if (!$courseId || empty($records)) {
            $this->redirectWith('/attendance', 'error', 'No attendance data provided.');
            return;
        }

        foreach ($records as $studentId => $status) {
            $dto = new AttendanceDTO(
                student_id: (int)$studentId,
                course_id: $courseId,
                attendance_date: $date,
                status: (string)$status
            );
            $this->attendanceService->recordAttendance($dto, auth_id());
        }

        $this->redirectWith("/attendance?course_id={$courseId}&date={$date}", 'success', 'Attendance roll-call saved successfully!');
    }
}
