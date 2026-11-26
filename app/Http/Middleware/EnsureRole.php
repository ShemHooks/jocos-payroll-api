<?php

namespace App\Http\Middleware;

use App\Enums\RoleCode;
use App\Models\User;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        if (!$user instanceof User) {
            return ApiResponse::error(
                'Unauthenticated.',
                401
            );
        }

        if (
            !$user->is_active ||
            $user->must_change_password ||
            !$user->tokenCan('*')
        ) {
            return ApiResponse::error(
                'Access denied.',
                403
            );
        }

        if (empty($roles)) {
            return ApiResponse::error(
                'Role access is not configured.',
                403
            );
        }

        foreach ($roles as $role) {
            if (RoleCode::tryFrom($role) === null) {
                return ApiResponse::error(
                    'Invalid role configuration.',
                    403
                );
            }
        }

        $hasRole = $user->roles()
            ->whereIn('roles.code', $roles)
            ->exists();

        if (!$hasRole) {
            return ApiResponse::error(
                'You do not have the required role.',
                403,
                [
                    'code' => 'ROLE_ACCESS_DENIED',
                ]
            );
        }

        return $next($request);
    }
}