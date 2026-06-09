<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $this->get('/')->assertOk()->assertSee('Horilla HRMS');
    }

    public function test_employee_is_redirected_to_employee_dashboard_and_blocked_from_admin(): void
    {
        $user = User::factory()->create(['access_level' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Anita',
            'last_name' => 'Oliver',
            'email' => 'anita@example.test',
            'is_active' => true,
        ]);
        $project = Project::create(['name' => 'Office Design', 'status' => 'active']);
        $project->assignees()->attach($employee->id, ['assigned_at' => now()]);

        $this->actingAs($user);

        $this->get('/')->assertRedirect(route('employee.dashboard'));
        $this->get(route('employee.dashboard'))->assertOk()->assertSee('Office Design');
        $this->get(route('employees.index'))->assertForbidden();
    }
}
