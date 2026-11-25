<?php

namespace Database\Seeders;

use App\Enums\PermissionCode as P;
use App\Enums\RoleCode as R;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $encoderPermissions = [
            P::DEPARTMENTS_VIEW,
            P::POSITIONS_VIEW,
            P::EMPLOYEES_VIEW,
            P::CONTRACTS_VIEW,

            P::PAYROLL_VIEW,
            P::PAYROLL_CREATE,
            P::PAYROLL_UPDATE,
            P::PAYROLL_SUBMIT,
        ];

        $rolePermissions = [

                // SYSTEM ADMINISTRATOR
            R::ADMIN->value => [
                P::USERS_VIEW,
                P::USERS_MANAGE,

                P::ROLES_VIEW,
                P::ROLES_MANAGE,
                P::PERMISSIONS_VIEW,

                P::ORGANIZATIONAL_UNITS_VIEW,
                P::ORGANIZATIONAL_UNITS_MANAGE,
                P::ORGANIZATIONAL_ASSIGNMENTS_MANAGE,

                P::DEPARTMENTS_VIEW,
                P::DEPARTMENTS_MANAGE,

                P::POSITIONS_VIEW,
                P::POSITIONS_MANAGE,

                P::EMPLOYEES_VIEW,
                P::EMPLOYEES_CREATE,
                P::EMPLOYEES_UPDATE,
                P::EMPLOYEE_ACCOUNTS_MANAGE,

                P::CONTRACTS_VIEW,
                P::CONTRACTS_MANAGE,

                P::PAYROLL_VIEW,
            ],

                // ACCOUNTING OFFICER
            R::ACCOUNTING->value => [
                P::DEPARTMENTS_VIEW,
                P::POSITIONS_VIEW,
                P::EMPLOYEES_VIEW,
                P::CONTRACTS_VIEW,

                P::PAYROLL_VIEW,
                P::PAYROLL_REVIEW,
            ],

                // TREASURER
            R::TREASURER->value => [
                P::DEPARTMENTS_VIEW,
                P::POSITIONS_VIEW,
                P::EMPLOYEES_VIEW,
                P::CONTRACTS_VIEW,

                P::PAYROLL_VIEW,
                P::TREASURY_EXPORT,
            ],

                // PAYROLL ENCODER
            R::PAYROLL_ENCODER->value =>
                $encoderPermissions,

                // HOSPITAL ENCODER
            R::HOSPITAL_ENCODER->value =>
                $encoderPermissions,

                // EMPLOYEE SELF-SERVICE
            R::EMPLOYEE->value => [
                P::PAYROLL_SELF_VIEW,
                P::PAYSLIP_SELF_DOWNLOAD,
            ],
        ];

        DB::transaction(function () use ($rolePermissions) {
            foreach ($rolePermissions as $roleCode => $permissions) {
                $role = Role::where('code', $roleCode)
                    ->firstOrFail();

                $permissionCodes = array_map(
                    fn(P $permission) => $permission->value,
                    $permissions
                );

                $permissionIds = Permission::whereIn(
                    'code',
                    $permissionCodes
                )->pluck('id');

                if (
                    $permissionIds->count()
                    !== count($permissionCodes)
                ) {
                    throw new \RuntimeException(
                        "Missing permissions for role: {$roleCode}"
                    );
                }

                $role->permissions()->sync(
                    $permissionIds->all()
                );

                $this->command?->info(
                    "{$roleCode}: {$permissionIds->count()} permissions assigned."
                );
            }
        });

        $this->command?->info(
            'Role permissions initialized successfully.'
        );
    }
}