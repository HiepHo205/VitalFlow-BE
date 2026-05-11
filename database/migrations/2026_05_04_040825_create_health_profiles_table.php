<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('age');
            $table->string('gender')->nullable();

            $table->decimal('height_cm', 5, 2);
            $table->decimal('weight_kg', 5, 2);

            $table->string('work_type')->nullable();

            $table->decimal('baseline_sleep_hours', 4, 2)->nullable();
            $table->integer('baseline_stress_level')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profiles');
    }
};
