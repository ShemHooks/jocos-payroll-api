<?php

namespace App\Http\Middleware;

use App\Enums\PermissionCode;
use App\Models\User;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$permissions
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

        if (empty($permissions)) {
            return ApiResponse::error(
                'Permission access is not configured.',
                403
            );
        }

        $userPermissions = $user->getPermissionCodes();

        foreach ($permissions as $permission) {
            if (
                PermissionCode::tryFrom($permission) === null ||
                !in_array(
                    $permission,
                    $userPermissions,
                    true
                )
            ) {
                return ApiResponse::error(
                    'You do not have the required permissions.',
                    403,
                    [
                        'code' => 'PERMISSION_ACCESS_DENIED',
                    ]
                );
            }
        }

        return $next($request);
    }
}