<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_settings', function (Blueprint $table): void {
            $table->json('employee_document_types')->nullable()->after('default_currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('master_settings', function (Blueprint $table): void {
            $table->dropColumn('employee_document_types');
        });
    }
};
