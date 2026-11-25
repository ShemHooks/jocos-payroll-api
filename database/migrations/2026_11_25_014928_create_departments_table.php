<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organizational_unit_id')
                ->nullable()
                ->constrained('organizational_units')
                ->restrictOnDelete();

            $table->string('office_code', 30)->unique();

            $table->string('name', 255);

            $table->string('abbreviation', 50)
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};