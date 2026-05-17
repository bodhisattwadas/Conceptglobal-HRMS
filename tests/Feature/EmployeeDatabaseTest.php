<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\JobRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_with_work_information(): void
    {
        $company = Company::create(['name' => 'Acme HR', 'city' => 'Kochi']);
        $department = Department::create(['name' => 'Human Resources']);
        $department->companies()->attach($company);
        $position = JobPosition::create(['department_id' => $department->id, 'name' => 'HR Executive']);
        $role = JobRole::create(['job_position_id' => $position->id, 'name' => 'Recruiter']);

        $response = $this->post(route('employees.store'), [
            'badge_id' => 'EMP-100',
            'first_name' => 'Ananya',
            'last_name' => 'Rao',
            'email' => 'ananya@example.test',
            'phone' => '9999900000',
            'gender' => 'female',
            'qualification' => 'MBA',
            'experience_years' => 4,
            'marital_status' => 'single',
            'company_id' => $company->id,
            'department_id' => $department->id,
            'job_position_id' => $position->id,
            'job_role_id' => $role->id,
            'work_email' => 'ananya.rao@acme.test',
            'date_joining' => '2026-01-10',
            'employment_type' => 'Permanent',
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', [
            'email' => 'ananya@example.test',
            'qualification' => 'MBA',
        ]);
        $this->assertDatabaseHas('employee_work_information', [
            'company_id' => $company->id,
            'department_id' => $department->id,
            'email' => 'ananya.rao@acme.test',
        ]);
    }

    public function test_employee_can_be_archived_and_restored(): void
    {
        $employee = Employee::create([
            'first_name' => 'Dev',
            'last_name' => 'Nair',
            'email' => 'dev@example.test',
        ]);

        $this->patch(route('employees.archive', $employee))->assertSessionHas('status', 'Employee archived.');
        $this->assertFalse($employee->fresh()->is_active);

        $this->patch(route('employees.restore', $employee))->assertSessionHas('status', 'Employee restored.');
        $this->assertTrue($employee->fresh()->is_active);
    }
}
