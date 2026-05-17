<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('cv_file_path')->nullable()->after('profile_photo_url');
            $table->json('related_document_paths')->nullable()->after('cv_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['cv_file_path', 'related_document_paths']);
        });
    }
};
