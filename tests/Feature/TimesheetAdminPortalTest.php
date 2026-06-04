<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimesheetAdminPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_timesheet_list_hides_old_manual_rows_and_shows_desktop_synced_rows(): void
    {
        $employee = Employee::create([
            'first_name' => 'Abigail',
            'last_name' => 'Peterson',
            'email' => 'abigail@example.test',
            'is_active' => true,
        ]);
        $project = Project::create([
            'name' => 'Office Design',
            'code' => 'OFFICE',
            'status' => 'active',
        ]);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);

        Timesheet::create([
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2022-02-10',
            'hours_spent' => 3,
            'description' => 'Old seeded manual row',
            'status' => 'approved',
            'source' => 'manual',
        ]);

        Timesheet::create([
            'desktop_uuid' => '16fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2026-06-04',
            'hours_spent' => 0.15,
            'timer_elapsed_seconds' => 540,
            'description' => 'Desktop synced row',
            'status' => 'draft',
            'source' => 'desktop',
        ]);

        $response = $this->get(route('timesheets.index'));

        $response->assertOk();
        $response->assertSee('Desktop synced row');
        $response->assertDontSee('Old seeded manual row');
    }
}

