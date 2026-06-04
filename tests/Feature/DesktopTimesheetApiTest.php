<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DesktopTimesheetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_login_and_sync_desktop_timesheet_with_logs(): void
    {
        $user = User::create([
            'name' => 'Abigail Peterson',
            'email' => 'abigail@example.test',
            'password' => Hash::make('secret-password'),
        ]);
        $employee = Employee::create([
            'user_id' => $user->id,
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

        $login = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.employee.id', $employee->id)
            ->assertJsonStructure(['token']);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => '16fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'start_time' => '09:08 AM',
                'end_time' => '09:17 AM',
                'hours_spent' => 0.15,
                'timer_elapsed_seconds' => 540,
                'timer_logs' => [
                    ['action' => 'Started', 'time' => '04/06/2026 09:08:48 AM', 'elapsed' => '00:00:00', 'total' => '00:00:00', 'note' => ''],
                    ['action' => 'Stopped by button', 'time' => '04/06/2026 09:17:48 AM', 'elapsed' => '00:09:00', 'total' => '00:09:00', 'note' => ''],
                ],
                'description' => 'Worked on layout',
                'is_billable' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('timesheet.project.name', 'Office Design')
            ->assertJsonPath('timesheet.task.title', 'Meeting Room Furnitures')
            ->assertJsonPath('timesheet.timer_elapsed_seconds', 540)
            ->assertJsonPath('timesheet.timer_logs.1.action', 'Stopped by button');

        $this->assertDatabaseHas('timesheets', [
            'employee_id' => $employee->id,
            'desktop_uuid' => '16fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
            'source' => 'desktop',
            'status' => 'draft',
        ]);
    }

    public function test_employee_can_submit_desktop_timesheet_as_final(): void
    {
        $user = User::create([
            'name' => 'Abigail Peterson',
            'email' => 'abigail@example.test',
            'password' => Hash::make('secret-password'),
        ]);
        Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Abigail',
            'last_name' => 'Peterson',
            'email' => 'abigail@example.test',
            'is_active' => true,
        ]);
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => '26fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 1,
                'timer_elapsed_seconds' => 3600,
                'description' => 'Final work',
                'is_billable' => true,
                'submit_final' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('timesheet.status', 'submitted');

        $this->assertDatabaseHas('timesheets', [
            'desktop_uuid' => '26fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
            'status' => 'submitted',
            'submitted_by' => $user->id,
        ]);
    }
}
