<?php

use App\Http\Controllers\AuthController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/health', function () {
        return ApiResponse::success([
            'service' => 'PGNO Payroll API',
            'version' => 'v1',
            'status' => 'healthy',
        ], 'API is operational.');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::post('/login', [
            AuthController::class,
            'login',
        ])->middleware('throttle:login');

        Route::middleware([
            'auth:sanctum',
            'account.active',
        ])->group(function () {

            Route::get('/me', [
                AuthController::class,
                'me',
            ]);

            Route::post('/change-password', [
                AuthController::class,
                'changePassword',
            ])->middleware('throttle:auth-change-password');

            Route::post('/logout', [
                AuthController::class,
                'logout',
            ]);

            Route::get('/permissions', [
                AuthController::class,
                'permissions',
            ])->middleware('password.changed');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Business Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'auth:sanctum',
        'account.active',
        'password.changed',
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | System Administration
        |--------------------------------------------------------------------------
        */

        // User management
        // Role management
        // Organizational unit management
        // Organizational assignments
        // HRIS department access mappings

        /*
        |--------------------------------------------------------------------------
        | HRIS Integration
        |--------------------------------------------------------------------------
        */

        // HRIS reference data
        // HRIS employee records
        // HRIS appointment records
        // Synchronization requests

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        // Synchronization review
        // Synchronization approval
        // Approved record synchronization
        // Payroll employee eligibility
        // Appointment approval history

        /*
        |--------------------------------------------------------------------------
        | Payroll Management
        |--------------------------------------------------------------------------
        */

        // Approved employee registry
        // Payroll preparation
        // Payroll submission
        // Payroll review

        /*
        |--------------------------------------------------------------------------
        | Treasury
        |--------------------------------------------------------------------------
        */

        // Approved payroll records
        // Treasury exports

    });
});