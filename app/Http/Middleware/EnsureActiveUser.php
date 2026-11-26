<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (
            !$user instanceof User
            || !$user->currentAccessToken() instanceof PersonalAccessToken
        ) {
            return ApiResponse::error(
                'Unauthenticated.',
                401
            );
        }

        try {
            $this->authService->validateAccount($user);
        } catch (ValidationException $exception) {
            $user->tokens()->delete();

            return ApiResponse::error(
                'Account unavailable.',
                401
            );
        }

        return $next($request);
    }
}