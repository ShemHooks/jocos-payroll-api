<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureOrganizationalAccess;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'account.active' =>
                EnsureActiveUser::class,

            'password.changed' =>
                EnsurePasswordChanged::class,

            'role' =>
                EnsureRole::class,

            'permission' =>
                EnsurePermission::class,

            'organization.access' =>
                EnsureOrganizationalAccess::class,
        ]);

        $middleware->trimStrings(except: [
            'current_password',
            'password',
            'password_confirmation',
            'new_password',
            'confirm_password',
            'temporary_password',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            function (Request $request, \Throwable $e) {
                return $request->is('api/*')
                    || $request->expectsJson();
            }
        );

        $exceptions->render(
            function (ValidationException $exception, Request $request) {
                if (!$request->is('api/*')) {
                    return null;
                }

                $message = $exception->status === 401
                    ? 'Authentication failed.'
                    : 'Validation failed.';

                return ApiResponse::error(
                    $message,
                    $exception->status,
                    $exception->errors()
                );
            }
        );

        $exceptions->render(
            function (AuthenticationException $exception, Request $request) {
                if (!$request->is('api/*')) {
                    return null;
                }

                return ApiResponse::error(
                    'Unauthenticated.',
                    401
                );
            }
        );
    })
    ->create();