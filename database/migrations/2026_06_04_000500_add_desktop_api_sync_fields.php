<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('desktop_api_token_hash', 64)->nullable()->after('remember_token');
            $table->timestamp('desktop_api_token_last_used_at')->nullable()->after('desktop_api_token_hash');
        });

        Schema::table('timesheets', function (Blueprint $table): void {
            $table->uuid('desktop_uuid')->nullable()->after('id');
            $table->unique('desktop_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table): void {
            $table->dropUnique(['desktop_uuid']);
            $table->dropColumn('desktop_uuid');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['desktop_api_token_hash', 'desktop_api_token_last_used_at']);
        });
    }
};

