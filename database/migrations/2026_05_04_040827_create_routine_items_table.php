<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('routine_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('category')->nullable();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->integer('duration_minutes')->nullable();

            $table->integer('priority')->default(1);

            $table->string('recurrence_type')->default('daily');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_items');
    }
};
