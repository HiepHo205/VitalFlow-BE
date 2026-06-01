<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->time('work_hours_start')->nullable()->after('work_type');
            $table->time('work_hours_end')->nullable()->after('work_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->dropColumn(['work_hours_start', 'work_hours_end']);
        });
    }
};
