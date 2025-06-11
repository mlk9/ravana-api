<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


if (!function_exists('sampleError')) {
    function sampleError(array $options = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'response_code' => $options['code'] ?? 422,
            'message' => $options['message'] ?? __('Something went wrong. Please try again or contact customer support.'),
            'errors' => $options['errors'] ?? null,
        ], $options['code'] ?? 422);
    }
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => 'Not Found.',
                    'errors' => ['route' => ['This route not found!']],
                    'code' => $e->getStatusCode()
                ]);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'errors' => ['server' => [__('An unexpected error occurred and we have notified our support team. Please try again later.')]],
                    'code' => $e->getStatusCode()
                ]);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => $e->getMessage(),
                    'errors' => ['server' => [__('An unexpected error occurred and we have notified our support team. Please try again later.')]],
                    'code' => $e->getStatusCode()
                ]);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => 'Unauthenticated',
                    'errors' => ['auth' => ['You are not authenticated.']],
                    'code' => $e->getStatusCode()
                ]);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => 'Unauthenticated',
                    'errors' => ['auth' => ['You are not authenticated.']],
                    'code' => 403
                ]);
            }
        });

        $exceptions->render(function (Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                    'code' => 422
                ]);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return sampleError([
                    'message' => $e->getMessage(),
                    'code' => 403
                ]);
            }
        });
    })->create();
