<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\DTOs\Attendance\AttendanceDTO;
use App\Http\Requests\Attendance\RecordAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;

class ApiAttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'attendance');

        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $date     = (string)$request->input('date', date('Y-m-d'));

        $overview = $this->attendanceService->getAttendanceView($courseId, $date);
        $overview['roster'] = AttendanceResource::collection($overview['roster'], $request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Attendance roster retrieved.',
            'data'    => $overview
        ], 200);
    }

    public function record(RecordAttendanceRequest $request): Response
    {
        $this->attendanceService->recordAttendance($request->toDTO(), auth_id());

        return Response::rawJson([
            'success' => true,
            'message' => 'Attendance recorded successfully.'
        ], 200);
    }
}
