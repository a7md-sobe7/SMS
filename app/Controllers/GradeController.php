<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\GradeRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\AuditLogRepository;

class GradeController extends Controller
{
    private GradeRepository $gradeRepo;
    private EnrollmentRepository $enrollmentRepo;
    private CourseRepository $courseRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->gradeRepo = new GradeRepository();
        $this->enrollmentRepo = new EnrollmentRepository();
        $this->courseRepo = new CourseRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $courses = $this->courseRepo->findAllWithDetails();

        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = $this->courseRepo->findWithDetails($courseId);
            $roster = $this->enrollmentRepo->getCourseRoster($courseId);
        }

        $this->render('grades/index', [
            'pageTitle'      => 'Academic Grade Entry & Evaluation',
            'courses'        => $courses,
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId
        ]);
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
            Session::flash('error', 'Please enter valid numerical grades between 0 and 100.');
            redirect('/grades');
        }

        $assignment = (float)$data['assignment_grade'];
        $midterm    = (float)$data['midterm_grade'];
        $final      = (float)$data['final_grade'];

        // Load Institutional Grade Configuration
        $gradingConfig = require dirname(__DIR__, 2) . '/config/grading.php';
        $weights = $gradingConfig['weights'];

        $totalGrade = round(
            ($assignment * $weights['assignment']) +
            ($midterm * $weights['midterm']) +
            ($final * $weights['final']),
            2
        );

        // Determine Letter Grade from Scale
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

        $this->auditRepo->log(
            auth_id(),
            'GRADE_UPDATED',
            'Grade',
            $gradeId,
            ['enrollment_id' => $data['enrollment_id'], 'total' => $totalGrade, 'letter' => $letterGrade]
        );

        $courseId = $request->input('course_id');
        $redirectUrl = $courseId ? "/grades?course_id={$courseId}" : "/grades";
        $this->redirectWith($redirectUrl, 'success', "Grade recorded: {$totalGrade}% ({$letterGrade})");
    }
}
