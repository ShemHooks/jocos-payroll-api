<?php

namespace App\Enums;

enum PermissionCode: string
{
    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    case USERS_VIEW = 'users.view';
    case USERS_MANAGE = 'users.manage';

    /*
    |--------------------------------------------------------------------------
    | Role and Permission Management
    |--------------------------------------------------------------------------
    */

    case ROLES_VIEW = 'roles.view';
    case ROLES_MANAGE = 'roles.manage';
    case PERMISSIONS_VIEW = 'permissions.view';

    /*
    |--------------------------------------------------------------------------
    | Organizational Access Management
    |--------------------------------------------------------------------------
    */

    case ORGANIZATIONAL_UNITS_VIEW = 'organizational_units.view';
    case ORGANIZATIONAL_UNITS_MANAGE = 'organizational_units.manage';

    case ORGANIZATIONAL_ASSIGNMENTS_MANAGE =
    'organizational_assignments.manage';

    case DEPARTMENT_MAPPINGS_VIEW =
    'department_mappings.view';

    case DEPARTMENT_MAPPINGS_MANAGE =
    'department_mappings.manage';

    /*
    |--------------------------------------------------------------------------
    | HRIS Reference Data
    |--------------------------------------------------------------------------
    */

    case DEPARTMENTS_VIEW = 'departments.view';
    case POSITIONS_VIEW = 'positions.view';
    case HRIS_REFERENCES_VIEW = 'hris.references.view';

    /*
    |--------------------------------------------------------------------------
    | HRIS Employee and Appointment Records
    |--------------------------------------------------------------------------
    */

    case HRIS_EMPLOYEES_VIEW = 'hris.employees.view';
    case HRIS_APPOINTMENTS_VIEW = 'hris.appointments.view';

    /*
    |--------------------------------------------------------------------------
    | HRIS Synchronization Workflow
    |--------------------------------------------------------------------------
    */

    case HRIS_SYNC_VIEW = 'hris.sync.view';
    case HRIS_SYNC_REVIEW = 'hris.sync.review';
    case HRIS_SYNC_APPROVE = 'hris.sync.approve';
    case HRIS_SYNC_EXECUTE = 'hris.sync.execute';
    case HRIS_SYNC_RETRY = 'hris.sync.retry';

    /*
    |--------------------------------------------------------------------------
    | Local Payroll Employee Registry
    |--------------------------------------------------------------------------
    */

    case EMPLOYEES_VIEW = 'employees.view';
    case EMPLOYEES_STATUS_MANAGE = 'employees.status.manage';

    /*
    |--------------------------------------------------------------------------
    | Approved Appointment Records
    |--------------------------------------------------------------------------
    */

    case APPOINTMENT_APPROVALS_VIEW =
    'appointment_approvals.view';

    case APPOINTMENT_APPROVALS_REVOKE =
    'appointment_approvals.revoke';

    /*
    |--------------------------------------------------------------------------
    | Payroll Management
    |--------------------------------------------------------------------------
    */

    case PAYROLL_VIEW = 'payroll.view';
    case PAYROLL_CREATE = 'payroll.create';
    case PAYROLL_UPDATE = 'payroll.update';
    case PAYROLL_SUBMIT = 'payroll.submit';
    case PAYROLL_REVIEW = 'payroll.review';

    /*
    |--------------------------------------------------------------------------
    | Treasury
    |--------------------------------------------------------------------------
    */

    case TREASURY_EXPORT = 'treasury.export';

    public function description(): string
    {
        return match ($this) {

            self::USERS_VIEW =>
                'View system user accounts.',

            self::USERS_MANAGE =>
                'Create and manage authorized system user accounts.',

            self::ROLES_VIEW =>
                'View system roles.',

            self::ROLES_MANAGE =>
                'Manage authorized role assignments and configuration.',

            self::PERMISSIONS_VIEW =>
                'View available system permissions.',

            self::ORGANIZATIONAL_UNITS_VIEW =>
                'View organizational units.',

            self::ORGANIZATIONAL_UNITS_MANAGE =>
                'Manage local organizational units.',

            self::ORGANIZATIONAL_ASSIGNMENTS_MANAGE =>
                'Manage user assignments to organizational units.',

            self::DEPARTMENT_MAPPINGS_VIEW =>
                'View HRIS department access mappings.',

            self::DEPARTMENT_MAPPINGS_MANAGE =>
                'Manage mappings between HRIS departments and local organizational units.',

            self::DEPARTMENTS_VIEW =>
                'View official HRIS departments.',

            self::POSITIONS_VIEW =>
                'View official HRIS positions.',

            self::HRIS_REFERENCES_VIEW =>
                'View authorized HRIS reference data.',

            self::HRIS_EMPLOYEES_VIEW =>
                'View official employee records from HRIS.',

            self::HRIS_APPOINTMENTS_VIEW =>
                'View official appointment records from HRIS.',

            self::HRIS_SYNC_VIEW =>
                'View HRIS synchronization requests.',

            self::HRIS_SYNC_REVIEW =>
                'Review and return pending HRIS synchronization requests.',

            self::HRIS_SYNC_APPROVE =>
                'Approve reviewed HRIS synchronization requests.',

            self::HRIS_SYNC_EXECUTE =>
                'Synchronize approved HRIS records into the local payroll database.',

            self::HRIS_SYNC_RETRY =>
                'Retry failed approved synchronization requests.',

            self::EMPLOYEES_VIEW =>
                'View authorized employees registered in payroll.',

            self::EMPLOYEES_STATUS_MANAGE =>
                'Manage local payroll employee eligibility status.',

            self::APPOINTMENT_APPROVALS_VIEW =>
                'View approved appointment snapshots and version history.',

            self::APPOINTMENT_APPROVALS_REVOKE =>
                'Revoke approved appointment records subject to authorization rules.',

            self::PAYROLL_VIEW =>
                'View authorized payroll records.',

            self::PAYROLL_CREATE =>
                'Create payroll records within authorized organizational scope.',

            self::PAYROLL_UPDATE =>
                'Update editable payroll records within authorized organizational scope.',

            self::PAYROLL_SUBMIT =>
                'Submit prepared payroll records for review.',

            self::PAYROLL_REVIEW =>
                'Review submitted payroll records.',

            self::TREASURY_EXPORT =>
                'Generate authorized treasury payroll exports.',
        };
    }
}