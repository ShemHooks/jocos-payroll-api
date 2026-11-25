<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->foreignId('position_id')
                ->constrained('positions')
                ->restrictOnDelete();

            $table->string('employment_type', 30);

            $table->decimal('salary_rate', 15, 2);

            $table->string('salary_period', 20);

            $table->date('start_date');

            $table->date('end_date')
                ->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->timestamps();

            $table->index(
                ['employee_id', 'start_date', 'end_date'],
                'idx_employee_contract_period'
            );

            $table->index(
                ['department_id', 'status'],
                'idx_department_contract_status'
            );

            $table->index(
                ['status', 'end_date'],
                'idx_contract_expiration'
            );
        });

        DB::statement("
            ALTER TABLE employment_contracts
            ADD CONSTRAINT chk_contract_dates
            CHECK (
                end_date IS NULL
                OR end_date >= start_date
            )
        ");

        DB::statement("
            ALTER TABLE employment_contracts
            ADD CONSTRAINT chk_contract_salary
            CHECK (salary_rate >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};