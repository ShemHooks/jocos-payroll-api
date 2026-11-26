<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ======================================
        // LOGIN RATE LIMITING
        // ======================================

        RateLimiter::for(
            'auth-login',
            function (Request $request) {
                $input = $request->input('username');

                $username = is_string($input)
                    ? Str::lower(trim($input))
                    : '';

                $usernameKey = hash(
                    'sha256',
                    $username
                );

                return [
                    Limit::perMinute(5)
                        ->by(
                            'login-user:'
                            . $usernameKey
                            . ':'
                            . $request->ip()
                        ),

                    Limit::perMinute(30)
                        ->by(
                            'login-ip:'
                            . $request->ip()
                        ),
                ];
            }
        );

        // ======================================
        // PASSWORD CHANGE RATE LIMITING
        // ======================================

        RateLimiter::for(
            'auth-change-password',
            function (Request $request) {
                return Limit::perMinute(5)
                    ->by(
                        'password-change:'
                        . (
                            $request->user()?->id
                            ?? $request->ip()
                        )
                    );
            }
        );
    }
}