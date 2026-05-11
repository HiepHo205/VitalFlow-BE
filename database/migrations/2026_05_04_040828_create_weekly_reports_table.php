<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('week_start');
            $table->date('week_end');

            $table->text('summary');

            $table->decimal('avg_mood', 4, 2)->nullable();
            $table->decimal('avg_energy', 4, 2)->nullable();
            $table->decimal('avg_sleep', 4, 2)->nullable();

            $table->decimal('burnout_risk_score', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
