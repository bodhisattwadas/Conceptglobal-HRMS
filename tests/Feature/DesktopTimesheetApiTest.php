<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
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
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $login = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
            'machine' => [
                'ip' => '192.168.1.20',
                'mac' => 'AA:BB:CC:DD:EE:FF',
            ],
        ]);

        $login->assertOk()
            ->assertJsonPath('user.employee.id', $employee->id)
            ->assertJsonStructure(['token']);

        $token = $login->json('token');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'desktop_last_login_machine_ip' => '192.168.1.20',
            'desktop_last_login_machine_mac' => 'AA:BB:CC:DD:EE:FF',
        ]);

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
        $employee = Employee::create([
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
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);

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
                'machine' => [
                    'ip' => '192.168.1.21',
                    'mac' => '11:22:33:44:55:66',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('timesheet.status', 'submitted')
            ->assertJsonPath('timesheet.desktop_submitted_machine_ip', '192.168.1.21')
            ->assertJsonPath('timesheet.desktop_submitted_machine_mac', '11:22:33:44:55:66');

        $this->assertDatabaseHas('timesheets', [
            'desktop_uuid' => '26fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
            'status' => 'submitted',
            'submitted_by' => $user->id,
            'desktop_submitted_machine_ip' => '192.168.1.21',
            'desktop_submitted_machine_mac' => '11:22:33:44:55:66',
        ]);
    }

    public function test_final_desktop_timesheet_cannot_be_changed_back_to_draft(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $uuid = '36fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb';

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => $uuid,
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 1,
                'description' => 'Final work',
                'submit_final' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('timesheet.status', 'submitted');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => $uuid,
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 2,
                'description' => 'Changed after final',
                'submit_final' => false,
            ])
            ->assertStatus(409);

        $this->assertDatabaseHas('timesheets', [
            'employee_id' => $employee->id,
            'desktop_uuid' => $uuid,
            'status' => 'submitted',
            'description' => 'Final work',
        ]);
        $this->assertSame(1.0, (float) Timesheet::where('desktop_uuid', $uuid)->value('hours_spent'));
    }

    public function test_resumed_draft_without_desktop_uuid_is_updated_not_duplicated(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);
        $draft = Timesheet::create([
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2026-06-04',
            'hours_spent' => 1,
            'description' => 'Original draft',
            'is_billable' => true,
            'status' => 'draft',
            'source' => 'desktop',
        ]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'id' => $draft->id,
                'desktop_uuid' => '46fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 2,
                'description' => 'Updated draft',
                'is_billable' => true,
                'submit_final' => false,
            ])
            ->assertOk()
            ->assertJsonPath('timesheet.id', $draft->id)
            ->assertJsonPath('timesheet.desktop_uuid', '46fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb')
            ->assertJsonPath('timesheet.description', 'Updated draft');

        $this->assertSame(1, Timesheet::where('employee_id', $employee->id)->count());
        $this->assertDatabaseHas('timesheets', [
            'id' => $draft->id,
            'desktop_uuid' => '46fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
            'hours_spent' => 2,
            'description' => 'Updated draft',
            'status' => 'draft',
        ]);
    }

    public function test_desktop_timesheet_can_move_between_running_and_draft(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $uuid = '76fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb';

        $running = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => $uuid,
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 0.0001,
                'timer_elapsed_seconds' => 0,
                'description' => 'Running work',
                'status' => 'running',
            ]);

        $running->assertCreated()
            ->assertJsonPath('timesheet.status', 'running');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'id' => $running->json('timesheet.id'),
                'desktop_uuid' => $uuid,
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 0.25,
                'timer_elapsed_seconds' => 900,
                'description' => 'Stopped work',
                'status' => 'draft',
            ])
            ->assertOk()
            ->assertJsonPath('timesheet.status', 'draft')
            ->assertJsonPath('timesheet.timer_elapsed_seconds', 900);

        $this->assertSame(1, Timesheet::where('employee_id', $employee->id)->count());
        $this->assertDatabaseHas('timesheets', [
            'desktop_uuid' => $uuid,
            'status' => 'draft',
            'hours_spent' => 0.25,
        ]);
    }

    public function test_employee_cannot_have_two_running_desktop_timesheets(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);
        Timesheet::create([
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2026-06-04',
            'hours_spent' => 0.0001,
            'description' => 'Already running',
            'is_billable' => true,
            'status' => 'running',
            'source' => 'desktop',
            'desktop_uuid' => '86fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
        ]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/desktop/timesheets', [
                'desktop_uuid' => '96fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'date' => '2026-06-04',
                'hours_spent' => 0.0001,
                'timer_elapsed_seconds' => 0,
                'description' => 'Second running',
                'status' => 'running',
            ])
            ->assertStatus(409);

        $this->assertSame(1, Timesheet::where('employee_id', $employee->id)->where('status', 'running')->count());
    }

    public function test_employee_can_delete_own_draft_desktop_timesheet(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $timesheet = Timesheet::create([
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2026-06-04',
            'hours_spent' => 1,
            'description' => 'Draft to delete',
            'is_billable' => true,
            'status' => 'draft',
            'source' => 'desktop',
            'desktop_uuid' => '56fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
        ]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/desktop/timesheets/'.$timesheet->id)
            ->assertOk()
            ->assertJsonPath('message', 'Timesheet deleted.');

        $this->assertDatabaseMissing('timesheets', [
            'id' => $timesheet->id,
        ]);
    }

    public function test_employee_cannot_delete_final_desktop_timesheet(): void
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
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Meeting Room Furnitures',
            'planned_hours' => 40,
            'status' => 'in_progress',
        ]);
        $timesheet = Timesheet::create([
            'employee_id' => $employee->id,
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'date' => '2026-06-04',
            'hours_spent' => 1,
            'description' => 'Submitted entry',
            'is_billable' => true,
            'status' => 'submitted',
            'source' => 'desktop',
            'desktop_uuid' => '66fc8d0c-2c8f-48a5-aa09-8021c5c9c9bb',
        ]);

        $token = $this->postJson('/api/desktop/login', [
            'email' => 'abigail@example.test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/desktop/timesheets/'.$timesheet->id)
            ->assertStatus(409);

        $this->assertDatabaseHas('timesheets', [
            'id' => $timesheet->id,
            'status' => 'submitted',
        ]);
    }
}
