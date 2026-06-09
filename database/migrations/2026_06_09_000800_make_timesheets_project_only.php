<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_employee', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'employee_id']);
        });

        $assignments = DB::table('project_task_assignees')
            ->join('project_tasks', 'project_tasks.id', '=', 'project_task_assignees.project_task_id')
            ->select('project_tasks.project_id', 'project_task_assignees.employee_id', 'project_task_assignees.assigned_at')
            ->distinct()
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('project_employee')->updateOrInsert(
                [
                    'project_id' => $assignment->project_id,
                    'employee_id' => $assignment->employee_id,
                ],
                [
                    'assigned_at' => $assignment->assigned_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Schema::table('timesheets', function (Blueprint $table): void {
            $table->dropForeign(['project_task_id']);
        });

        Schema::table('timesheets', function (Blueprint $table): void {
            $table->foreignId('project_task_id')->nullable()->change();
            $table->foreign('project_task_id')->references('id')->on('project_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table): void {
            $table->dropForeign(['project_task_id']);
        });

        Schema::table('timesheets', function (Blueprint $table): void {
            $table->foreignId('project_task_id')->nullable(false)->change();
            $table->foreign('project_task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
        });

        Schema::dropIfExists('project_employee');
    }
};
