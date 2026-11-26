<?php

namespace Database\Seeders;

use App\Enums\PermissionCode;
use App\Enums\RoleCode;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('mariadb')->transaction(function () {
            $permissionIds = Permission::query()
                ->pluck('id', 'code');

            foreach ($this->assignments() as $roleCode => $permissions) {
                $role = Role::query()
                    ->where('code', $roleCode)
                    ->firstOrFail();

                $ids = [];

                foreach ($permissions as $permission) {
                    $permissionId = $permissionIds->get(
                        $permission->value
                    );

                    if ($permissionId === null) {
                        throw new RuntimeException(
                            "Missing permission: {$permission->value}"
                        );
                    }

                    $ids[] = $permissionId;
                }

                $role->permissions()->sync(
                    array_values(array_unique($ids))
                );
            }
        });
    }

    private function assignments(): array
    {
        $referencePermissions = [
            PermissionCode::DEPARTMENTS_VIEW,
            PermissionCode::POSITIONS_VIEW,
            PermissionCode::HRIS_REFERENCES_VIEW,
        ];

        $payrollReadPermissions = [
            PermissionCode::EMPLOYEES_VIEW,
            PermissionCode::APPOINTMENT_APPROVALS_VIEW,
            PermissionCode::PAYROLL_VIEW,
        ];

        $encoderPermissions = [
            ...$referencePermissions,
            ...$payrollReadPermissions,

            PermissionCode::PAYROLL_CREATE,
            PermissionCode::PAYROLL_UPDATE,
            PermissionCode::PAYROLL_SUBMIT,
        ];

        return [

                /*
                |--------------------------------------------------------------------------
                | Administrator
                |--------------------------------------------------------------------------
                */

            RoleCode::ADMIN->value => [
                PermissionCode::USERS_VIEW,
                PermissionCode::USERS_MANAGE,

                PermissionCode::ROLES_VIEW,
                PermissionCode::ROLES_MANAGE,
                PermissionCode::PERMISSIONS_VIEW,

                PermissionCode::ORGANIZATIONAL_UNITS_VIEW,
                PermissionCode::ORGANIZATIONAL_UNITS_MANAGE,
                PermissionCode::ORGANIZATIONAL_ASSIGNMENTS_MANAGE,

                PermissionCode::DEPARTMENT_MAPPINGS_VIEW,
                PermissionCode::DEPARTMENT_MAPPINGS_MANAGE,

                ...$referencePermissions,
                ...$payrollReadPermissions,
            ],

                /*
                |--------------------------------------------------------------------------
                | Accounting
                |--------------------------------------------------------------------------
                */

            RoleCode::ACCOUNTING->value => [
                ...$referencePermissions,
                ...$payrollReadPermissions,

                PermissionCode::HRIS_EMPLOYEES_VIEW,
                PermissionCode::HRIS_APPOINTMENTS_VIEW,

                PermissionCode::HRIS_SYNC_VIEW,
                PermissionCode::HRIS_SYNC_REVIEW,
                PermissionCode::HRIS_SYNC_APPROVE,
                PermissionCode::HRIS_SYNC_EXECUTE,
                PermissionCode::HRIS_SYNC_RETRY,

                PermissionCode::EMPLOYEES_STATUS_MANAGE,
                PermissionCode::APPOINTMENT_APPROVALS_REVOKE,

                PermissionCode::PAYROLL_REVIEW,
            ],

                /*
                |--------------------------------------------------------------------------
                | Treasurer
                |--------------------------------------------------------------------------
                */

            RoleCode::TREASURER->value => [
                ...$referencePermissions,
                ...$payrollReadPermissions,

                PermissionCode::TREASURY_EXPORT,
            ],

                /*
                |--------------------------------------------------------------------------
                | Provincial Payroll Encoder
                |--------------------------------------------------------------------------
                */

            RoleCode::PAYROLL_ENCODER->value => [
                ...$encoderPermissions,
            ],

                /*
                |--------------------------------------------------------------------------
                | Hospital Payroll Encoder
                |--------------------------------------------------------------------------
                */

            RoleCode::HOSPITAL_ENCODER->value => [
                ...$encoderPermissions,
            ],
        ];
    }
}