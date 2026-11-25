<?php

namespace App\Enums;

enum PermissionCode: string
{
    // User and access management
    case USERS_VIEW = 'users.view';
    case USERS_MANAGE = 'users.manage';

    case ROLES_VIEW = 'roles.view';
    case ROLES_MANAGE = 'roles.manage';

    case PERMISSIONS_VIEW = 'permissions.view';

    // Organizational units
    case ORGANIZATIONAL_UNITS_VIEW =
    'organizational_units.view';

    case ORGANIZATIONAL_UNITS_MANAGE =
    'organizational_units.manage';

    case ORGANIZATIONAL_ASSIGNMENTS_MANAGE =
    'organizational_assignments.manage';

    // Departments
    case DEPARTMENTS_VIEW = 'departments.view';
    case DEPARTMENTS_MANAGE = 'departments.manage';

    // Positions
    case POSITIONS_VIEW = 'positions.view';
    case POSITIONS_MANAGE = 'positions.manage';

    // Employees
    case EMPLOYEES_VIEW = 'employees.view';
    case EMPLOYEES_CREATE = 'employees.create';
    case EMPLOYEES_UPDATE = 'employees.update';

    case EMPLOYEE_ACCOUNTS_MANAGE =
    'employee_accounts.manage';

    // Employment contracts
    case CONTRACTS_VIEW = 'contracts.view';
    case CONTRACTS_MANAGE = 'contracts.manage';

    // Payroll
    case PAYROLL_VIEW = 'payroll.view';
    case PAYROLL_CREATE = 'payroll.create';
    case PAYROLL_UPDATE = 'payroll.update';
    case PAYROLL_SUBMIT = 'payroll.submit';
    case PAYROLL_REVIEW = 'payroll.review';

    // Treasury
    case TREASURY_EXPORT = 'treasury.export';

    // Employee self-service
    case PAYROLL_SELF_VIEW = 'payroll.self.view';
    case PAYSLIP_SELF_DOWNLOAD = 'payslip.self.download';

    public function description(): string
    {
        return match ($this) {
            self::USERS_VIEW =>
                'View system user accounts.',

            self::USERS_MANAGE =>
                'Create and manage system user accounts.',

            self::ROLES_VIEW =>
                'View system roles.',

            self::ROLES_MANAGE =>
                'Manage authorized role configurations.',

            self::PERMISSIONS_VIEW =>
                'View system permissions.',

            self::ORGANIZATIONAL_UNITS_VIEW =>
                'View organizational units.',

            self::ORGANIZATIONAL_UNITS_MANAGE =>
                'Manage organizational units.',

            self::ORGANIZATIONAL_ASSIGNMENTS_MANAGE =>
                'Manage user organizational assignments.',

            self::DEPARTMENTS_VIEW =>
                'View department records.',

            self::DEPARTMENTS_MANAGE =>
                'Create and update department records.',

            self::POSITIONS_VIEW =>
                'View position records.',

            self::POSITIONS_MANAGE =>
                'Create and update position records.',

            self::EMPLOYEES_VIEW =>
                'View authorized employee records.',

            self::EMPLOYEES_CREATE =>
                'Register employee records.',

            self::EMPLOYEES_UPDATE =>
                'Update employee records.',

            self::EMPLOYEE_ACCOUNTS_MANAGE =>
                'Provision and manage employee accounts.',

            self::CONTRACTS_VIEW =>
                'View authorized employment contracts.',

            self::CONTRACTS_MANAGE =>
                'Create and manage employment contracts.',

            self::PAYROLL_VIEW =>
                'View authorized payroll records.',

            self::PAYROLL_CREATE =>
                'Create payroll records.',

            self::PAYROLL_UPDATE =>
                'Update editable payroll records.',

            self::PAYROLL_SUBMIT =>
                'Submit payroll records for review.',

            self::PAYROLL_REVIEW =>
                'Perform authorized accounting payroll review.',

            self::TREASURY_EXPORT =>
                'Export authorized payroll records for treasury.',

            self::PAYROLL_SELF_VIEW =>
                'View personal released payroll records.',

            self::PAYSLIP_SELF_DOWNLOAD =>
                'Download personal released payslips.',
        };
    }
}