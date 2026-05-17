<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('profile_photo_url')->nullable()->after('badge_id');
            $table->string('card_color', 20)->default('#6f42c1')->after('profile_photo_url');
        });

        Schema::table('employee_work_information', function (Blueprint $table): void {
            $table->foreignId('coach_id')->nullable()->after('reporting_manager_id')->constrained('employees')->nullOnDelete();
            $table->string('work_mobile', 30)->nullable()->after('email');
            $table->string('work_phone', 30)->nullable()->after('work_mobile');
            $table->string('work_location', 150)->nullable()->after('employment_type');
            $table->string('working_hours', 100)->nullable()->after('work_location');
            $table->string('timezone', 80)->nullable()->after('working_hours');
        });

        Schema::create('employee_bank_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 80)->nullable();
            $table->string('account_holder_name', 160)->nullable();
            $table->string('ifsc_code', 40)->nullable();
            $table->string('branch', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bank_details');

        Schema::table('employee_work_information', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coach_id');
            $table->dropColumn([
                'work_mobile',
                'work_phone',
                'work_location',
                'working_hours',
                'timezone',
            ]);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['profile_photo_url', 'card_color']);
        });
    }
};
