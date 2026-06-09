<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_work_information') && Schema::hasColumn('employee_work_information', 'job_role_id')) {
            Schema::table('employee_work_information', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('job_role_id');
            });
        }

        Schema::dropIfExists('company_job_role');
        Schema::dropIfExists('job_roles');
    }

    public function down(): void
    {
        Schema::create('job_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_position_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('modified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['job_position_id', 'name']);
        });

        Schema::create('company_job_role', function (Blueprint $table): void {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['company_id', 'job_role_id']);
        });

        if (Schema::hasTable('employee_work_information') && ! Schema::hasColumn('employee_work_information', 'job_role_id')) {
            Schema::table('employee_work_information', function (Blueprint $table): void {
                $table->foreignId('job_role_id')->nullable()->after('job_position_id')->constrained()->nullOnDelete();
            });
        }
    }
};
