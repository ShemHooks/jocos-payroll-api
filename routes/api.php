<?php

use Illuminate\Support\Facades\Route;
use App\Support\ApiResponse;

Route::prefix('v1')->group(function () {

    Route::get('/health', function () {
        return ApiResponse::success([
            'application' => 'JOCOS Payroll 2.0',
            'version' => '1.0.0',
            'status' => 'healthy',
        ], 'JOCOS Payroll API is running');
    });

});