<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('qualification', 100)->nullable()->after('date_of_birth');
            $table->unsignedSmallInteger('experience_years')->nullable()->after('qualification');
            $table->string('marital_status', 50)->nullable()->after('experience_years');
            $table->unsignedSmallInteger('children_count')->nullable()->after('marital_status');
            $table->string('emergency_contact_name', 100)->nullable()->after('children_count');
            $table->string('emergency_contact', 30)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'qualification',
                'experience_years',
                'marital_status',
                'children_count',
                'emergency_contact_name',
                'emergency_contact',
                'emergency_contact_relation',
            ]);
        });
    }
};
