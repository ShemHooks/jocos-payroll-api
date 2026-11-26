<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error(
                'Unauthenticated.',
                401
            );
        }

        if (
            $user->must_change_password
            || !$user->tokenCan('*')
        ) {
            return ApiResponse::error(
                'You must change your password before continuing.',
                403,
                [
                    'code' => 'PASSWORD_CHANGE_REQUIRED',
                ]
            );
        }

        return $next($request);
    }
}