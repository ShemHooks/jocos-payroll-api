<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_organizational_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('organizational_unit_id')
                ->constrained('organizational_units')
                ->restrictOnDelete();

            $table->unique(
                ['user_id', 'organizational_unit_id'],
                'uq_user_organizational_unit'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_organizational_units');
    }
};