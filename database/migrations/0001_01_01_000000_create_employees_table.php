<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // HRIS reference
            $table->unsignedBigInteger('hris_employee_id')
                ->unique();

            // Employee information
            $table->string('employee_no', 50)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 80)->nullable();
            $table->string('last_name', 80);
            $table->string('name_extension', 10)->nullable();

            // HRIS employment status reference
            $table->unsignedInteger('hris_status_id');

            $table->date('original_hire_date')->nullable();
            $table->date('separation_date')->nullable();

            // Payroll-specific status
            $table->string('payroll_status', 20)
                ->default('on_hold');

            // HRIS synchronization
            $table->string('sync_status', 30)
                ->default('synced');

            $table->char('hris_source_hash', 64);

            $table->timestamp('hris_updated_at')
                ->nullable();

            $table->dateTime('hris_synced_at');

            $table->timestamp('hris_last_checked_at')
                ->nullable();

            $table->timestamps();

            // Indexes
            $table->index(
                ['last_name', 'first_name'],
                'idx_employee_name'
            );

            $table->index('payroll_status');
            $table->index('sync_status');
            $table->index('hris_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};