<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hris_sync_requests', function (Blueprint $table) {
            $table->id();

            // External HRIS references
            $table->unsignedBigInteger('hris_employee_id');

            $table->unsignedBigInteger('hris_appointment_id')
                ->nullable();

            // Local employee reference
            // Null for employees awaiting initial synchronization
            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->restrictOnDelete();

            // Request classification
            $table->string('request_type', 40);

            $table->string('status', 30)
                ->default('pending');

            // Previously approved information
            $table->json('previous_snapshot')
                ->nullable();

            // HRIS information presented for review
            $table->json('source_snapshot');

            // Source version verification
            $table->char('source_hash', 64);

            // Detection and review
            $table->timestamp('detected_at')
                ->useCurrent();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable();

            // Approval
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            // Synchronization
            $table->timestamp('synced_at')
                ->nullable();

            $table->unsignedInteger('sync_attempts')
                ->default(0);

            $table->timestamp('last_sync_attempt_at')
                ->nullable();

            // Review notes and synchronization errors
            $table->text('remarks')->nullable();

            $table->text('failure_reason')->nullable();

            // Prevent duplicate requests for the same detection event
            $table->char('deduplication_key', 64)
                ->unique();

            $table->timestamps();

            // Indexes
            $table->index('hris_employee_id');
            $table->index('hris_appointment_id');
            $table->index('request_type');

            $table->index(
                ['status', 'detected_at'],
                'idx_hris_sync_status_detected'
            );

            $table->index(
                ['hris_employee_id', 'status'],
                'idx_hris_sync_employee_status'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hris_sync_requests');
    }
};