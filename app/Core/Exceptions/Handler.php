<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Exceptions\BusinessException;
use App\Exceptions\CapacityExceededException;
use App\Exceptions\ModelNotFoundException;
use App\Exceptions\ValidationException;
use Throwable;

/**
 * Centralized Application Exception Handler
 * 
 * Intercepts uncaught domain exceptions and translates them into uniform,
 * structured JSON API errors or user-friendly HTML error pages/redirects.
 */
class Handler
{
    /**
     * Render an exception into an HTTP response or JSON payload.
     */
    public function render(Throwable $e, ?Request $request = null): Response|null
    {
        $this->report($e);

        $isApi = $request !== null && ($request->isAjax() || str_starts_with($request->getPath(), '/api/'));

        if ($isApi) {
            return $this->renderApiResponse($e);
        }

        return $this->renderWebResponse($e, $request);
    }

    /**
     * Log exception details to system log.
     */
    public function report(Throwable $e): void
    {
        // Skip spamming logs for standard client validation/auth errors
        if ($e instanceof ValidationException || $e instanceof AuthorizationException || $e instanceof AuthenticationException) {
            return;
        }

        error_log(sprintf(
            "[%s] Exception [%s]: %s in %s:%d\nStack Trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }

    /**
     * Format consistent JSON response for REST API clients.
     */
    private function renderApiResponse(Throwable $e): Response
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_SERVER_ERROR';
        $message = $e->getMessage();
        $errors = [];

        if ($e instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'VALIDATION_FAILED';
            $errors = $e->getErrors();
        } elseif ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $errorCode = 'UNAUTHENTICATED';
        } elseif ($e instanceof AuthorizationException) {
            $statusCode = 403;
            $errorCode = 'FORBIDDEN';
        } elseif ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'MODEL_NOT_FOUND';
        } elseif ($e instanceof CapacityExceededException) {
            $statusCode = 409;
            $errorCode = 'CAPACITY_EXCEEDED';
        } elseif ($e instanceof BusinessException) {
            $statusCode = $e->getCode() ?: 422;
            $errorCode = $e->getErrorCode();
        }

        $isDebug = function_exists('env') ? env('APP_DEBUG', false) : false;

        $payload = [
            'success' => false,
            'message' => $message,
            'code'    => $errorCode,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        if ($isDebug && $statusCode === 500) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => explode("\n", $e->getTraceAsString())
            ];
        }

        return Response::rawJson($payload, $statusCode);
    }

    /**
     * Format friendly HTML views or redirects for traditional Web requests.
     */
    private function renderWebResponse(Throwable $e, ?Request $request): Response|null
    {
        // 1. Validation Exception -> flash errors and redirect back
        if ($e instanceof ValidationException) {
            Session::flash('errors', $e->getErrors());
            Session::flash('_old_input', $request ? $request->all() : []);
            Session::flash('error', $e->getMessage());

            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            redirect($referer);
            return null;
        }

        // 2. Authentication Exception -> redirect to login
        if ($e instanceof AuthenticationException) {
            Session::flash('error', 'Please log in to continue.');
            redirect('/login');
            return null;
        }

        // 3. Authorization Exception -> 403 Forbidden view
        if ($e instanceof AuthorizationException) {
            http_response_code(403);
            $forbiddenView = dirname(__DIR__, 2) . '/views/errors/403.php';
            if (file_exists($forbiddenView)) {
                $exception = $e;
                require $forbiddenView;
            } else {
                echo "<h1>403 Forbidden</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
            }
            return null;
        }

        // 4. Model / Page Not Found -> 404 View
        if ($e instanceof ModelNotFoundException) {
            http_response_code(404);
            $notFoundView = dirname(__DIR__, 2) . '/views/errors/404.php';
            if (file_exists($notFoundView)) {
                $exception = $e;
                $message = $e->getMessage();
                require $notFoundView;
            } else {
                echo "<h1>404 Not Found</h1><p>" . htmlspecialchars($message) . "</p>";
            }
            return null;
        }

        // 5. Business / Capacity Exception -> Flash error and redirect back
        if ($e instanceof BusinessException || $e instanceof CapacityExceededException) {
            Session::flash('error', $e->getMessage());
            Session::flash('_old_input', $request ? $request->all() : []);
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            redirect($referer);
            return null;
        }

        // 6. Generic Internal Server Error -> 500 View
        http_response_code(500);
        $errorFile = dirname(__DIR__, 2) . '/views/errors/500.php';
        if (file_exists($errorFile)) {
            $exception = $e;
            require $errorFile;
        } else {
            echo "<h1>500 Internal Server Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }

        return null;
    }
}
