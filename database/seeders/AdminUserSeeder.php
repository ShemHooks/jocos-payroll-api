<?php

namespace Database\Seeders;

use App\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('jocos.admin.name'));
        $username = trim((string) config('jocos.admin.username'));
        $email = trim((string) config('jocos.admin.email'));
        $password = (string) config('jocos.admin.password');

        $email = $email !== '' ? $email : null;

        if ($name === '' || mb_strlen($name) > 150) {
            throw new RuntimeException(
                'Invalid administrator name.'
            );
        }

        if ($username === '' || mb_strlen($username) > 120) {
            throw new RuntimeException(
                'Invalid administrator username.'
            );
        }

        if (
            $email !== null &&
            (
                mb_strlen($email) > 255 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)
            )
        ) {
            throw new RuntimeException(
                'Invalid administrator email address.'
            );
        }

        DB::connection('mariadb')->transaction(function () use ($name, $username, $email, $password) {
            $adminRole = Role::query()
                ->where('code', RoleCode::ADMIN->value)
                ->firstOrFail();

            $existingUser = User::query()
                ->where('username', $username)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Protect Existing Administrator Accounts
            |--------------------------------------------------------------------------
            */

            if ($existingUser) {
                $roles = $existingUser->roles()
                    ->pluck('roles.code')
                    ->all();

                if ($roles !== [RoleCode::ADMIN->value]) {
                    throw new RuntimeException(
                        'The configured administrator username belongs to an account with invalid role assignments.'
                    );
                }

                if (!$existingUser->is_active) {
                    throw new RuntimeException(
                        'The configured administrator account is inactive.'
                    );
                }

                $this->command?->info(
                    'Administrator already exists. No changes made.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Initial Password
            |--------------------------------------------------------------------------
            */

            if (
                mb_strlen($password) < 12 ||
                strlen($password) > 72 ||
                str_contains($password, "\0") ||
                !preg_match('/[A-Z]/', $password) ||
                !preg_match('/[a-z]/', $password) ||
                !preg_match('/[0-9]/', $password) ||
                !preg_match('/[^a-zA-Z0-9\s]/', $password)
            ) {
                throw new RuntimeException(
                    'Administrator password must contain at least 12 characters, uppercase and lowercase letters, a number, and a symbol, with a maximum of 72 bytes.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Email
            |--------------------------------------------------------------------------
            */

            if (
                $email !== null &&
                User::query()->where('email', $email)->exists()
            ) {
                throw new RuntimeException(
                    'The administrator email address is already in use.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Initial Administrator
            |--------------------------------------------------------------------------
            */

            $user = new User();

            $user->name = $name;
            $user->username = $username;
            $user->email = $email;
            $user->password = Hash::make($password);

            $user->is_active = true;
            $user->must_change_password = true;
            $user->password_reset_at = now();
            $user->password_changed_at = null;

            $user->save();

            $user->roles()->attach($adminRole->id);

            $this->command?->info(
                'Initial administrator created successfully.'
            );
        });
    }
}