<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('desktop_last_login_machine_ip', 45)->nullable()->after('desktop_api_token_last_used_at');
            $table->string('desktop_last_login_machine_mac', 17)->nullable()->after('desktop_last_login_machine_ip');
        });

        Schema::table('timesheets', function (Blueprint $table): void {
            $table->string('desktop_submitted_machine_ip', 45)->nullable()->after('desktop_uuid');
            $table->string('desktop_submitted_machine_mac', 17)->nullable()->after('desktop_submitted_machine_ip');
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table): void {
            $table->dropColumn(['desktop_submitted_machine_ip', 'desktop_submitted_machine_mac']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['desktop_last_login_machine_ip', 'desktop_last_login_machine_mac']);
        });
    }
};
