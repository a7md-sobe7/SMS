<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\GradeRepository;
use App\Repositories\AuditLogRepository;

class ApiGradeController extends Controller
{
    private GradeRepository $gradeRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->gradeRepo = new GradeRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function upsert(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'enrollment_id'    => 'required|numeric',
            'assignment_grade' => 'required|numeric|min:0|max:100',
            'midterm_grade'    => 'required|numeric|min:0|max:100',
            'final_grade'      => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            json_response(null, 422, 'Validation failed.', $validator->errors());
        }

        $assignment = (float)$data['assignment_grade'];
        $midterm    = (float)$data['midterm_grade'];
        $final      = (float)$data['final_grade'];

        $gradingConfig = require dirname(__DIR__, 3) . '/config/grading.php';
        $weights = $gradingConfig['weights'];

        $totalGrade = round(
            ($assignment * $weights['assignment']) +
            ($midterm * $weights['midterm']) +
            ($final * $weights['final']),
            2
        );

        $letterGrade = 'F';
        $remarks = 'Fail';

        foreach ($gradingConfig['scale'] as $tier) {
            if ($totalGrade >= $tier['min'] && $totalGrade <= $tier['max']) {
                $letterGrade = $tier['letter'];
                $remarks = $tier['remark'];
                break;
            }
        }

        $gradeId = $this->gradeRepo->upsert([
            'enrollment_id'    => (int)$data['enrollment_id'],
            'assignment_grade' => $assignment,
            'midterm_grade'    => $midterm,
            'final_grade'      => $final,
            'total_grade'      => $totalGrade,
            'letter_grade'     => $letterGrade,
            'remarks'          => $remarks
        ]);

        $this->auditRepo->log(auth_id(), 'API_GRADE_UPDATED', 'Grade', $gradeId);
        $grade = $this->gradeRepo->find($gradeId);

        json_response($grade, 200, 'Grade recorded successfully.');
    }
}
