<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timesheet_settings', function (Blueprint $table): void {
            $table->unsignedInteger('desktop_timer_timeout_seconds')->default(10)->after('maximum_hours_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('timesheet_settings', function (Blueprint $table): void {
            $table->dropColumn('desktop_timer_timeout_seconds');
        });
    }
};
