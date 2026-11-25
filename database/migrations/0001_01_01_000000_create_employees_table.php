<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_no', 50)->unique();

            $table->string('first_name', 150);
            $table->string('middle_name', 150)->nullable();
            $table->string('last_name', 150);
            $table->string('name_extension', 30)->nullable();

            $table->date('birth_date')->nullable();
            $table->string('sex', 20)->nullable();

            $table->string('email', 255)->nullable();
            $table->string('mobile_number', 30)->nullable();

            $table->timestamps();

            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};