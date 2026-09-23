<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Grade\UpsertGradeRequest;
use App\Services\GradeService;

class GradeController extends Controller
{
    public function __construct(
        private GradeService $gradeService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'grade');

        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $overview = $this->gradeService->getGradeOverview($courseId);

        $this->render('grades/index', [
            'pageTitle'      => 'Academic Grade Entry & Evaluation',
            'courses'        => $overview['courses'],
            'selectedCourse' => $overview['selectedCourse'],
            'roster'         => $overview['roster'],
            'courseId'       => $courseId
        ]);
    }

    public function upsert(UpsertGradeRequest $request): void
    {
        $dto = $request->toDTO();
        $result = $this->gradeService->evaluateAndUpsertGrade($dto, auth_id());

        $courseId = $request->input('course_id');
        $redirectUrl = $courseId ? "/grades?course_id={$courseId}" : "/grades";
        $this->redirectWith($redirectUrl, 'success', "Grade recorded: {$result['total_grade']}% ({$result['letter_grade']})");
    }
}
