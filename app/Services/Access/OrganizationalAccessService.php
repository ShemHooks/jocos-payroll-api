<?php

namespace App\Services\Access;

use App\Enums\OrganizationalUnitType;
use App\Enums\RoleCode;
use App\Models\DepartmentAccessMapping;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class OrganizationalAccessService
{
    /*
    |--------------------------------------------------------------------------
    | Global Organizational Access
    |--------------------------------------------------------------------------
    */

    public function hasGlobalAccess(User $user): bool
    {
        if (
            !$user->is_active ||
            $user->must_change_password
        ) {
            return false;
        }

        $roles = $user->roles()
            ->pluck('roles.code')
            ->all();

        if (count($roles) !== 1) {
            return false;
        }

        return in_array($roles[0], [
            RoleCode::ADMIN->value,
            RoleCode::ACCOUNTING->value,
            RoleCode::TREASURER->value,
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Organizational Scope
    |--------------------------------------------------------------------------
    */

    public function resolve(User $user): array
    {
        if (
            !$user->is_active ||
            $user->must_change_password
        ) {
            $this->deny();
        }

        $roles = $user->roles()
            ->pluck('roles.code')
            ->all();

        if (count($roles) !== 1) {
            $this->deny();
        }

        $role = RoleCode::tryFrom($roles[0]);

        if ($role === null) {
            $this->deny();
        }

        if ($this->hasGlobalAccess($user)) {
            return [
                'global' => true,
                'organizational_unit_ids' => [],
                'hris_department_ids' => [],
            ];
        }

        if (!$role->isEncoder()) {
            $this->deny();
        }

        $units = $user->organizationalUnits()
            ->get();

        if ($units->isEmpty()) {
            $this->deny(
                'No organizational unit is assigned to your account.'
            );
        }

        /*
         * Do not silently ignore invalid assignments.
         */

        if (
            $units->contains(
                fn($unit) => !$unit->is_active
            )
        ) {
            $this->deny(
                'Your account has an inactive organizational assignment.'
            );
        }

        $expectedType = match ($role) {
            RoleCode::PAYROLL_ENCODER =>
                OrganizationalUnitType::PROVINCIAL_OFFICE,

            RoleCode::HOSPITAL_ENCODER =>
                OrganizationalUnitType::HOSPITAL,

            default => null,
        };

        if ($expectedType === null) {
            $this->deny();
        }

        if (
            $units->contains(
                fn($unit) => $unit->type !== $expectedType
            )
        ) {
            $this->deny(
                'Your organizational assignment is incompatible with your role.'
            );
        }

        /*
         * Hospital encoders are restricted to one hospital.
         */

        if (
            $role === RoleCode::HOSPITAL_ENCODER &&
            $units->count() !== 1
        ) {
            $this->deny(
                'Hospital encoders must be assigned to exactly one hospital.'
            );
        }

        $unitIds = $units
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $mappings = DepartmentAccessMapping::query()
            ->whereIn('organizational_unit_id', $unitIds)
            ->where('is_active', true)
            ->get([
                'organizational_unit_id',
                'hris_department_id',
            ]);

        /*
         * Every assigned unit must have an active mapping.
         */

        $mappedUnitIds = $mappings
            ->pluck('organizational_unit_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->all();

        if (!empty(array_diff($unitIds, $mappedUnitIds))) {
            $this->deny(
                'An assigned organizational unit has no active department mapping.'
            );
        }

        $departmentIds = $mappings
            ->pluck('hris_department_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($departmentIds)) {
            $this->deny(
                'No authorized departments are available.'
            );
        }

        return [
            'global' => false,
            'organizational_unit_ids' => $unitIds,
            'hris_department_ids' => $departmentIds,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Department Access
    |--------------------------------------------------------------------------
    */

    public function canAccessDepartment(
        User $user,
        int $hrisDepartmentId
    ): bool {
        if ($hrisDepartmentId <= 0) {
            return false;
        }

        $scope = $this->resolve($user);

        return $scope['global'] ||
            in_array(
                $hrisDepartmentId,
                $scope['hris_department_ids'],
                true
            );
    }

    public function authorizeDepartment(
        User $user,
        int $hrisDepartmentId
    ): void {
        if (
            !$this->canAccessDepartment(
                $user,
                $hrisDepartmentId
            )
        ) {
            $this->deny(
                'You are not authorized to access this department.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Organizational Unit Access
    |--------------------------------------------------------------------------
    */

    public function canAccessOrganizationalUnit(
        User $user,
        int $organizationalUnitId
    ): bool {
        if ($organizationalUnitId <= 0) {
            return false;
        }

        $scope = $this->resolve($user);

        return $scope['global'] ||
            in_array(
                $organizationalUnitId,
                $scope['organizational_unit_ids'],
                true
            );
    }

    public function authorizeOrganizationalUnit(
        User $user,
        int $organizationalUnitId
    ): void {
        if (
            !$this->canAccessOrganizationalUnit(
                $user,
                $organizationalUnitId
            )
        ) {
            $this->deny(
                'You are not authorized to access this organizational unit.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scoping
    |--------------------------------------------------------------------------
    */

    public function scopeByDepartment(
        Builder $query,
        User $user,
        string $column = 'department_id'
    ): Builder {
        $scope = $this->resolve($user);

        if ($scope['global']) {
            return $query;
        }

        return $query->whereIn(
            $column,
            $scope['hris_department_ids']
        );
    }

    public function scopeByOrganizationalUnit(
        Builder $query,
        User $user,
        string $column = 'organizational_unit_id'
    ): Builder {
        $scope = $this->resolve($user);

        if ($scope['global']) {
            return $query;
        }

        return $query->whereIn(
            $column,
            $scope['organizational_unit_ids']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Access Denial
    |--------------------------------------------------------------------------
    */

    private function deny(
        string $message = 'Organizational access denied.'
    ): never {
        throw new AuthorizationException($message);
    }
}