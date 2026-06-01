<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->foreignUuid('goal_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropForeign(['goal_id']);
            $table->dropColumn('goal_id');
        });
    }
};
