<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_feedbacks', function (Blueprint $table) {
            $table->foreignUuid('related_routine_id')
                ->nullable()
                ->after('related_log_id')
                ->constrained('routines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_feedbacks', function (Blueprint $table) {
            $table->dropForeign(['related_routine_id']);
            $table->dropColumn('related_routine_id');
        });
    }
};
