<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_appointment_approvals', function (Blueprint $table) {
            $table->id();

            // Local payroll employee
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            // External HRIS appointment reference
            $table->unsignedBigInteger('hris_appointment_id');

            // Originating synchronization request
            $table->foreignId('hris_sync_request_id')
                ->unique()
                ->constrained('hris_sync_requests')
                ->restrictOnDelete();

            // Approval version
            $table->unsignedInteger('version')
                ->default(1);

            // Payroll-relevant appointment snapshot
            $table->json('approved_snapshot');

            // Approved appointment data hash
            $table->char('source_hash', 64);

            // Approval status
            $table->string('status', 30)
                ->default('approved');

            // Accounting approval
            $table->foreignId('approved_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('approved_at');

            // Payroll coverage
            $table->date('payroll_effective_date');

            $table->date('payroll_end_date')
                ->nullable();

            // Latest approved version of this appointment
            $table->boolean('is_current')
                ->default(true);

            // Previous approval version
            $table->foreignId('supersedes_id')
                ->nullable()
                ->constrained('employee_appointment_approvals')
                ->restrictOnDelete();

            $table->timestamp('superseded_at')
                ->nullable();

            // Revocation information
            $table->foreignId('revoked_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('revoked_at')
                ->nullable();

            $table->text('revocation_reason')
                ->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            // Prevent duplicate approval versions
            $table->unique(
                ['hris_appointment_id', 'version'],
                'uq_appointment_approval_version'
            );

            // Indexes
            $table->index(
                ['employee_id', 'status'],
                'idx_employee_approval_status'
            );

            $table->index(
                ['hris_appointment_id', 'is_current'],
                'idx_appointment_current_approval'
            );

            $table->index(
                ['payroll_effective_date', 'payroll_end_date'],
                'idx_approval_payroll_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_appointment_approvals');
    }
};