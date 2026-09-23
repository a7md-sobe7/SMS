<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Grade\UpsertGradeRequest;
use App\Http\Resources\GradeResource;
use App\Services\GradeService;

class ApiGradeController extends Controller
{
    public function __construct(
        private GradeService $gradeService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'grade');

        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $overview = $this->gradeService->getGradeOverview($courseId);

        return Response::rawJson([
            'success' => true,
            'message' => 'Grades retrieved.',
            'data'    => $overview
        ], 200);
    }

    public function upsert(UpsertGradeRequest $request): Response
    {
        $dto = $request->toDTO();
        $result = $this->gradeService->evaluateAndUpsertGrade($dto, auth_id());

        return GradeResource::make($result)->toResponse($request, 200, 'Grade recorded successfully.');
    }
}
