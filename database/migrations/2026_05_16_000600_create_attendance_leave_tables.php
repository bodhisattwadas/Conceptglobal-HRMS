<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_devices', function (Blueprint $table): void {
            $table->id();
            $table->string('machine_ip', 45);
            $table->unsignedInteger('port')->default(124);
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('working_address')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->unsignedInteger('worked_minutes')->default(0);
            $table->string('source')->default('manual');
            $table->string('status', 30)->default('open');
            $table->timestamps();
            $table->index(['employee_id', 'attendance_date']);
        });

        Schema::create('attendance_regularization_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80);
            $table->text('reason')->nullable();
            $table->dateTime('from_at');
            $table->dateTime('to_at');
            $table->string('status', 30)->default('requested');
            $table->timestamps();
        });

        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('approval', 120)->default('Approved by Time Off Officer');
            $table->string('color', 20)->default('#6f5b9a');
            $table->boolean('is_paid')->default(true);
            $table->decimal('default_days', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('duration_days', 8, 2)->default(0);
            $table->decimal('duration_hours', 8, 2)->default(0);
            $table->decimal('remaining_legal_leaves', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('to_approve');
            $table->timestamps();
        });

        Schema::create('leave_pending_works', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->string('task');
            $table->string('project')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('email_alias_prefix')->nullable();
            $table->string('email_alias_domain')->nullable();
            $table->boolean('leave_reminder_enabled')->default(true);
            $table->unsignedInteger('leave_reminder_days_before')->default(3);
            $table->boolean('employee_shift_enabled')->default(true);
            $table->boolean('vacation_management_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_settings');
        Schema::dropIfExists('leave_pending_works');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('attendance_regularization_requests');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_devices');
    }
};
