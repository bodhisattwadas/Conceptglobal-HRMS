<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table): void {
            $table->unsignedInteger('timer_elapsed_seconds')->default(0)->after('hours_spent');
            $table->json('timer_logs')->nullable()->after('timer_elapsed_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table): void {
            $table->dropColumn(['timer_elapsed_seconds', 'timer_logs']);
        });
    }
};
