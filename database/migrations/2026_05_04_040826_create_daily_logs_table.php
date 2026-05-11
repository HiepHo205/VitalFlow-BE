<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('log_date');

            $table->integer('mood_score')->nullable();
            $table->integer('energy_level')->nullable();
            $table->integer('stress_level')->nullable();

            $table->decimal('sleep_hours', 4, 2)->nullable();
            $table->integer('water_intake_ml')->nullable();

            $table->text('body_condition')->nullable();

            $table->integer('productivity_score')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
