<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthUserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    // ==========================================
    // LOGIN
    // ==========================================

    public function login(
        LoginRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $result = $this->authService->login(
            $validated['username'],
            $validated['password']
        );

        return ApiResponse::success(
            $result,
            'Login successful.'
        );
    }

    // ==========================================
    // AUTHENTICATED USER
    // ==========================================

    public function me(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $user->load('roles');

        return ApiResponse::success(
            (new AuthUserResource($user))->resolve($request),
            'Authenticated user retrieved successfully.'
        );
    }

    // ==========================================
    // USER PERMISSIONS
    // ==========================================

    public function permissions(
        Request $request
    ): JsonResponse {
        return ApiResponse::success(
            $request->user()->getPermissionCodes(),
            'Permissions retrieved successfully.'
        );
    }

    // ==========================================
    // CHANGE PASSWORD
    // ==========================================

    public function changePassword(
        ChangePasswordRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $result = $this->authService->changePassword(
            $request->user(),
            $validated['current_password'],
            $validated['new_password']
        );

        return ApiResponse::success(
            $result,
            'Password changed successfully.'
        );
    }

    // ==========================================
    // LOGOUT
    // ==========================================

    public function logout(
        Request $request
    ): JsonResponse {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return ApiResponse::success(
            null,
            'Logout successful.'
        );
    }
}