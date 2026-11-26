<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Access\OrganizationalAccessService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationalAccess
{
    public function __construct(
        private readonly OrganizationalAccessService $access
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
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

        try {
            $scope = $this->access->resolve($user);
        } catch (AuthorizationException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                403,
                [
                    'code' => 'ORGANIZATIONAL_ACCESS_DENIED',
                ]
            );
        }

        $request->attributes->set(
            'organizational_scope',
            $scope
        );

        return $next($request);
    }
}