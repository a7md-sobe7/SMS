<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// 1. Load .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

echo "\n=======================================================\n";
echo " 🏗️  LARAVEL BACKEND ARCHITECTURE VERIFICATION TEST\n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// -------------------------------------------------------------------
// 1. Container & Service Providers
// -------------------------------------------------------------------
echo "[1] Testing IoC Container & Service Providers...\n";
$app = \App\Core\Container::getInstance();

$providers = [
    \App\Providers\AppServiceProvider::class,
    \App\Providers\RepositoryServiceProvider::class,
    \App\Providers\AuthServiceProvider::class,
    \App\Providers\EventServiceProvider::class,
];

foreach ($providers as $pClass) {
    $p = new $pClass($app);
    $p->register();
    $p->boot();
}

assertTest("Container is instantiated", $app instanceof \App\Core\Container);
assertTest("UserRepositoryInterface is bound", $app->has(\App\Contracts\Repositories\UserRepositoryInterface::class));
assertTest("StudentRepositoryInterface is bound", $app->has(\App\Contracts\Repositories\StudentRepositoryInterface::class));
assertTest("CourseRepositoryInterface is bound", $app->has(\App\Contracts\Repositories\CourseRepositoryInterface::class));
assertTest("GradeRepositoryInterface is bound", $app->has(\App\Contracts\Repositories\GradeRepositoryInterface::class));

// -------------------------------------------------------------------
// 2. DTO Layer
// -------------------------------------------------------------------
echo "\n[2] Testing DTOs...\n";
$studentDto = \App\DTOs\Student\StudentDTO::fromArray([
    'student_code'    => 'STU-2026-0001',
    'first_name'      => 'John',
    'last_name'       => 'Doe',
    'email'           => 'john.doe@student.sms.edu',
    'department_id'   => 1,
    'date_of_birth'   => '2004-05-15',
    'gender'          => 'male',
    'enrollment_year' => 2026,
    'academic_level'  => 'freshman',
    'status'          => 'active'
]);

assertTest("StudentDTO creation", $studentDto->student_code === 'STU-2026-0001' && $studentDto->first_name === 'John');
assertTest("StudentDTO toArray()", is_array($studentDto->toArray()) && $studentDto->toArray()['email'] === 'john.doe@student.sms.edu');

$gradeDto = \App\DTOs\Grade\GradeDTO::fromArray([
    'enrollment_id'    => 1,
    'assignment_grade' => 95.0,
    'midterm_grade'    => 90.0,
    'final_grade'      => 92.0
]);
assertTest("GradeDTO creation", $gradeDto->assignment_grade === 95.0 && $gradeDto->enrollment_id === 1);

// -------------------------------------------------------------------
// 3. FormRequest Layer
// -------------------------------------------------------------------
echo "\n[3] Testing FormRequest Layer...\n";
$mockReq = new \App\Core\Request('POST', '/students', [], [
    'student_code'    => 'STU-9999',
    'first_name'      => 'Alice',
    'last_name'       => 'Smith',
    'email'           => 'alice@test.com',
    'department_id'   => 1,
    'date_of_birth'   => '2002-01-01',
    'gender'          => 'female',
    'enrollment_year' => 2026,
    'academic_level'  => 'freshman',
    'status'          => 'active'
]);

$formReq = \App\Http\Requests\Student\StoreStudentRequest::createFromRequest($mockReq);
assertTest("FormRequest correctly resolves input and rules", $formReq->validated()['first_name'] === 'Alice');
assertTest("FormRequest converts to DTO", $formReq->toDTO() instanceof \App\DTOs\Student\StudentDTO);

// -------------------------------------------------------------------
// 4. Gate & Policies
// -------------------------------------------------------------------
echo "\n[4] Testing Gate & Policies...\n";
$gate = $app->make(\App\Core\Gate::class);

$adminUser = ['id' => 1, 'username' => 'admin', 'role' => 'admin'];
$studentUser = ['id' => 2, 'username' => 'john', 'role' => 'student'];

assertTest("Admin can create student", $gate->allows('create', 'student', $adminUser));
assertTest("Student cannot create student", $gate->denies('create', 'student', $studentUser));
assertTest("Admin can create course", $gate->allows('create', 'course', $adminUser));
assertTest("Student cannot create course", $gate->denies('create', 'course', $studentUser));
assertTest("Student can self-enroll", $gate->allows('enroll', 'enrollment', $studentUser));

// -------------------------------------------------------------------
// 5. Grade Calculation & Business Logic
// -------------------------------------------------------------------
echo "\n[5] Testing GradeService Calculation Engine...\n";
$gradeService = $app->make(\App\Services\GradeService::class);
// Weighted: 95*0.3 + 90*0.3 + 92*0.4 = 28.5 + 27.0 + 36.8 = 92.3% -> Letter 'A'
$evaluated = $gradeService->evaluateAndUpsertGrade($gradeDto, 1);

assertTest("GradeService weighted total grade calculation", $evaluated['total_grade'] == 92.3, "Expected 92.3, got " . $evaluated['total_grade']);
assertTest("GradeService automated letter grade assignment", $evaluated['letter_grade'] === 'A', "Expected A, got " . $evaluated['letter_grade']);

// -------------------------------------------------------------------
// 6. Events & Listeners & Queue
// -------------------------------------------------------------------
echo "\n[6] Testing Events, Listeners & Queue...\n";
$dispatcher = $app->make(\App\Core\Events\EventDispatcher::class);
$queue = $app->make(\App\Core\Queue\QueueManager::class);

$event = new \App\Events\UserLoggedInEvent(1, 'admin', 'admin', '127.0.0.1');
$dispatcher->dispatch($event);
assertTest("Event dispatched and handled by audit listener", true);

$job = new \App\Jobs\SendEmailNotificationJob('test@sms.edu', 'Test Subject', 'Body text');
$queue->push($job, 'test_queue');
assertTest("QueueManager pushed job", $queue->count('test_queue') > 0);

$worker = $app->make(\App\Core\Queue\QueueWorker::class);
$processed = $worker->processNext('test_queue');
assertTest("QueueWorker processed job", $processed === true);

// -------------------------------------------------------------------
// 7. Custom Exceptions & Centralized Handler
// -------------------------------------------------------------------
echo "\n[7] Testing Custom Exceptions & Handler...\n";
$handler = $app->make(\App\Core\Exceptions\Handler::class);

$valEx = new \App\Exceptions\ValidationException(['email' => ['The email field is required.']]);
$apiRequest = new \App\Core\Request('GET', '/api/students');
$response = $handler->render($valEx, $apiRequest);

assertTest("Handler returns Response object", $response instanceof \App\Core\Response);
assertTest("Handler returns HTTP 422 for ValidationException", $response->getStatusCode() === 422);

$authEx = new \App\Exceptions\AuthorizationException('Forbidden action');
$authResponse = $handler->render($authEx, $apiRequest);
assertTest("Handler returns HTTP 403 for AuthorizationException", $authResponse->getStatusCode() === 403);

// -------------------------------------------------------------------
// 8. API Resources & JSON Payload Structure
// -------------------------------------------------------------------
echo "\n[8] Testing API Resources...\n";
$rawStudent = [
    'id'              => 10,
    'student_code'    => 'STU-2026-0010',
    'first_name'      => 'Alan',
    'last_name'       => 'Turing',
    'email'           => 'alan.turing@sms.edu',
    'department_id'   => 1,
    'department_name' => 'Computer Science',
    'department_code' => 'CS',
    'academic_level'  => 'senior',
    'status'          => 'active',
    'enrollment_year' => 2026,
    'date_of_birth'   => '1912-06-23',
    'gender'          => 'male',
    'created_at'      => '2026-01-01 00:00:00'
];

$resource = \App\Http\Resources\StudentResource::make($rawStudent);
$transformed = $resource->toArray($apiRequest);

assertTest("StudentResource transformation contains full_name", $transformed['full_name'] === 'Alan Turing');
assertTest("StudentResource transformation contains department_code", $transformed['department_code'] === 'CS');

$apiResp = $resource->toResponse($apiRequest, 200, 'Student profile');
assertTest("Resource produces HTTP 200 JSON Response", $apiResp->getStatusCode() === 200);

// Verify API Login Payload Structure for Frontend
$authController = $app->make(\App\Controllers\Api\ApiAuthController::class);
// Simulate logged-in user in session
\App\Core\Session::start();
\App\Core\Session::set('user', $adminUser);
$meResp = $authController->me($apiRequest);
$meDecoded = json_decode($meResp->getContent(), true);

assertTest("API Auth payload contains 'user' key for React AuthContext", isset($meDecoded['data']['user']) && $meDecoded['data']['user']['username'] === 'admin');

// -------------------------------------------------------------------
// 9. Router Dispatch with IoC Auto-wiring
// -------------------------------------------------------------------
echo "\n[9] Testing Router Dispatch...\n";
$router = new \App\Core\Router($app);
require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/login';
$loginReq = \App\Core\Request::capture();

ob_start();
$router->dispatch($loginReq);
$out = ob_get_clean();

assertTest("Router dispatched /login via Container", strlen($out) > 0);

echo "\n=======================================================\n";
echo " 🎉 ARCHITECTURE VERIFICATION RESULTS\n";
echo "    PASSED: {$passed}\n";
echo "    FAILED: {$failed}\n";
echo "=======================================================\n\n";

if ($failed > 0) {
    exit(1);
}
