<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('planned_hours', 8, 2)->default(0);
            $table->decimal('spent_hours', 8, 2)->default(0);
            $table->decimal('remaining_hours', 8, 2)->default(0);
            $table->decimal('extra_hours', 8, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->string('status')->default('new');
            $table->boolean('is_recurrent')->default(false);
            $table->string('priority')->default('normal');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_task_assignees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['project_task_id', 'employee_id']);
        });

        Schema::create('timesheets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('hours_spent', 8, 2);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('source')->default('manual');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'date']);
            $table->index(['project_id', 'date']);
            $table->index(['project_task_id', 'date']);
            $table->index('status');
        });

        Schema::create('timesheet_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('timesheet_id')->constrained()->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        Schema::create('timesheet_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('allow_future_entries')->default(false);
            $table->integer('future_entry_limit_days')->default(0);
            $table->boolean('allow_employee_edit_after_submit')->default(false);
            $table->boolean('allow_employee_delete_after_submit')->default(false);
            $table->boolean('require_approval')->default(true);
            $table->decimal('minimum_hours_per_entry', 5, 2)->default(0.25);
            $table->decimal('maximum_hours_per_day', 5, 2)->default(24);
            $table->boolean('restrict_to_assigned_tasks')->default(false);
            $table->boolean('lock_after_payroll')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_settings');
        Schema::dropIfExists('timesheet_status_logs');
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('project_task_assignees');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
    }
};
