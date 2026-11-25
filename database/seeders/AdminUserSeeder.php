<?php

namespace Database\Seeders;

use App\Enums\RoleCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = config('jocos.admin.username');
        $email = config('jocos.admin.email') ?: null;

        if (!is_string($username) || trim($username) === '') {
            throw new RuntimeException(
                'JOCOS_ADMIN_USERNAME must be configured.'
            );
        }

        DB::transaction(function () use ($username, $email) {
            $adminRole = Role::where(
                'code',
                RoleCode::ADMIN->value
            )->firstOrFail();

            $existingUser = User::where(
                'username',
                $username
            )->lockForUpdate()->first();

            if ($existingUser) {
                $roleCodes = $existingUser->roles()
                    ->pluck('roles.code')
                    ->all();

                if (
                    $existingUser->employee_id !== null
                    || $roleCodes !== [RoleCode::ADMIN->value]
                ) {
                    throw new RuntimeException(
                        'The configured administrator username belongs to an incompatible account.'
                    );
                }

                $this->command?->warn(
                    'Administrator already exists. Existing account was not modified.'
                );

                return;
            }

            $password = config('jocos.admin.password');

            Validator::make([
                'username' => $username,
                'email' => $email,
                'password' => $password,
            ], [
                'username' => [
                    'required',
                    'string',
                    'max:120',
                    'unique:users,username',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:12',
                    'max:72',
                ],
            ])->validate();

            if (strlen($password) > 72) {
                throw new RuntimeException(
                    'Administrator password must not exceed 72 bytes.'
                );
            }

            $admin = new User();

            $admin->username = $username;
            $admin->email = $email;

            // Automatically hashed by the User model.
            $admin->password = $password;

            // Operational accounts are independent
            // from employee payroll records.
            $admin->employee_id = null;

            $admin->is_active = true;

            $admin->must_change_password = true;

            $admin->password_reset_at = now();

            $admin->password_changed_at = null;

            $admin->save();

            $admin->roles()->attach(
                $adminRole->id
            );

            $this->command?->info(
                'Initial System Administrator created successfully.'
            );
        });
    }
}