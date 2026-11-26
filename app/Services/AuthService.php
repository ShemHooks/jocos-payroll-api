<?php

namespace App\Services;

use App\Enums\RoleCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        string $username,
        string $password
    ): array {
        $username = trim($username);

        if (
            strlen($password) > 72 ||
            str_contains($password, "\0")
        ) {
            $this->invalidCredentials();
        }

        $user = User::query()
            ->where('username', $username)
            ->first();

        /*
         * Perform a password hash operation even when
         * the requested account does not exist.
         */

        if (!$user) {
            Hash::make($password);

            $this->invalidCredentials();
        }

        if (!Hash::check($password, $user->password)) {
            $this->invalidCredentials();
        }

        return DB::connection('mariadb')->transaction(
            function () use ($user, $password) {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->find($user->id);

                if (
                    !$lockedUser ||
                    !Hash::check(
                        $password,
                        $lockedUser->password
                    )
                ) {
                    $this->invalidCredentials();
                }

                $this->validateAccount($lockedUser);

                if (Hash::needsRehash($lockedUser->password)) {
                    $lockedUser->password = Hash::make($password);
                    $lockedUser->save();
                }

                return $this->issueToken($lockedUser);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Account Validation
    |--------------------------------------------------------------------------
    */

    public function validateAccount(User $user): void
    {
        if (!$user->is_active) {
            $this->invalidCredentials();
        }

        $roles = $user->roles()
            ->pluck('roles.code')
            ->unique()
            ->values()
            ->all();

        if (empty($roles)) {
            $this->invalidCredentials();
        }

        $supportedRoles = array_map(
            fn(RoleCode $role) => $role->value,
            RoleCode::cases()
        );

        foreach ($roles as $role) {
            if (!in_array($role, $supportedRoles, true)) {
                $this->invalidCredentials();
            }
        }

        /*
         * Administrator must be an exclusive role.
         */

        if (
            in_array(RoleCode::ADMIN->value, $roles, true) &&
            count($roles) !== 1
        ) {
            $this->invalidCredentials();
        }

        /*
         * Prevent conflicting encoder classifications.
         */

        $isPayrollEncoder = in_array(
            RoleCode::PAYROLL_ENCODER->value,
            $roles,
            true
        );

        $isHospitalEncoder = in_array(
            RoleCode::HOSPITAL_ENCODER->value,
            $roles,
            true
        );

        if ($isPayrollEncoder && $isHospitalEncoder) {
            $this->invalidCredentials();
        }

        /*
         * Prevent combining encoder responsibilities
         * with global accounting or treasury access.
         */

        if (
            ($isPayrollEncoder || $isHospitalEncoder) &&
            count($roles) !== 1
        ) {
            $this->invalidCredentials();
        }

        /*
         * Separate accounting and treasury responsibilities.
         */

        if (
            in_array(RoleCode::ACCOUNTING->value, $roles, true) &&
            in_array(RoleCode::TREASURER->value, $roles, true)
        ) {
            $this->invalidCredentials();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    public function changePassword(
        User $user,
        string $currentPassword,
        string $newPassword
    ): array {
        if (
            strlen($currentPassword) > 72 ||
            str_contains($currentPassword, "\0")
        ) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is invalid.',
                ],
            ]);
        }

        if (
            mb_strlen($newPassword) < 12 ||
            strlen($newPassword) > 72 ||
            str_contains($newPassword, "\0") ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/[0-9]/', $newPassword) ||
            !preg_match('/[^a-zA-Z0-9\s]/', $newPassword)
        ) {
            throw ValidationException::withMessages([
                'new_password' => [
                    'The new password does not meet the password requirements.',
                ],
            ]);
        }

        return DB::connection('mariadb')->transaction(
            function () use ($user, $currentPassword, $newPassword) {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->find($user->id);

                if (!$lockedUser) {
                    $this->invalidCredentials();
                }

                $this->validateAccount($lockedUser);

                /*
                 * Verify that the requesting token
                 * has not already been revoked.
                 */

                $currentToken = $user->currentAccessToken();

                if (!$currentToken) {
                    $this->invalidCredentials();
                }

                $token = $lockedUser->tokens()
                    ->whereKey($currentToken->getKey())
                    ->first();

                if (!$token) {
                    $this->invalidCredentials();
                }

                if (
                    $token->expires_at !== null &&
                    $token->expires_at->isPast()
                ) {
                    $this->invalidCredentials();
                }

                $expiration = config('sanctum.expiration');

                if (
                    $expiration !== null &&
                    $token->created_at->lte(
                        now()->subMinutes((int) $expiration)
                    )
                ) {
                    $this->invalidCredentials();
                }

                /*
                 * Verify current password.
                 */

                if (
                    !Hash::check(
                        $currentPassword,
                        $lockedUser->password
                    )
                ) {
                    throw ValidationException::withMessages([
                        'current_password' => [
                            'The current password is incorrect.',
                        ],
                    ]);
                }

                if (
                    Hash::check(
                        $newPassword,
                        $lockedUser->password
                    )
                ) {
                    throw ValidationException::withMessages([
                        'new_password' => [
                            'The new password must be different from the current password.',
                        ],
                    ]);
                }

                /*
                 * Update password and remove forced-change flag.
                 */

                $lockedUser->password = Hash::make($newPassword);
                $lockedUser->must_change_password = false;
                $lockedUser->password_changed_at = now();

                $lockedUser->save();

                /*
                 * Revoke all previous sessions.
                 */

                $lockedUser->tokens()->delete();

                return $this->issueToken($lockedUser);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Issue Sanctum Access Token
    |--------------------------------------------------------------------------
    */

    private function issueToken(User $user): array
    {
        $expirationMinutes = max(
            1,
            (int) (config('sanctum.expiration') ?? 480)
        );

        $expiresAt = now()->addMinutes($expirationMinutes);

        $abilities = $user->must_change_password
            ? ['password:change']
            : ['*'];

        $token = $user->createToken(
            'jocos-web',
            $abilities,
            $expiresAt
        );

        return [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $expirationMinutes * 60,
            'expires_at' => $expiresAt->toIso8601String(),
            'must_change_password' =>
                (bool) $user->must_change_password,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Generic Authentication Failure
    |--------------------------------------------------------------------------
    */

    private function invalidCredentials(): never
    {
        throw ValidationException::withMessages([
            'username' => [
                'Invalid credentials or unauthorized account.',
            ],
        ])->status(401);
    }
}