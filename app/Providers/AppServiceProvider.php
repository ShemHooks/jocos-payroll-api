<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure authentication rate limiters.
     */
    private function configureRateLimiting(): void
    {
        // Login protection.

        RateLimiter::for('login', function (Request $request) {
            $input = $request->input('username');

            $username = is_string($input)
                ? Str::lower(trim($input))
                : '';

            $ip = $request->ip();

            return [
                Limit::perMinute(5)->by(
                    'login:account:' . hash(
                        'sha256',
                        $username . '|' . $ip
                    )
                ),

                Limit::perMinute(30)->by(
                    'login:ip:' . $ip
                ),
            ];
        });

        // Password change protection.

        RateLimiter::for(
            'auth-change-password',
            function (Request $request) {
                $userId = $request->user()?->getAuthIdentifier();

                return Limit::perMinute(5)->by(
                    $userId !== null
                    ? 'password-change:user:' . $userId
                    : 'password-change:ip:' . $request->ip()
                );
            }
        );
    }
}