<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_with_work_information(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $company = Company::create(['name' => 'Acme HR', 'city' => 'Kochi']);
        $department = Department::create(['name' => 'Human Resources']);
        $department->companies()->attach($company);
        $position = JobPosition::create(['department_id' => $department->id, 'name' => 'HR Executive']);

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

        $employee = Employee::where('email', 'ananya@example.test')->firstOrFail();
        $this->assertSame('employee', $employee->user->access_level);
    }

    public function test_employee_can_be_created_with_admin_access_role(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $response = $this->post(route('employees.store'), [
            'first_name' => 'Admin',
            'last_name' => 'Employee',
            'email' => 'admin-employee@example.test',
            'access_level' => 'super_admin',
        ]);

        $response->assertRedirect(route('employees.index'));

        $employee = Employee::where('email', 'admin-employee@example.test')->firstOrFail();
        $this->assertSame('super_admin', $employee->user->access_level);
    }

    public function test_employee_access_role_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $employee = Employee::create([
            'first_name' => 'Role',
            'last_name' => 'Change',
            'email' => 'role-change@example.test',
        ]);

        $this->put(route('employees.update', $employee), [
            'first_name' => 'Role',
            'last_name' => 'Change',
            'email' => 'role-change@example.test',
            'access_level' => 'super_admin',
        ])->assertRedirect(route('employees.index'));

        $this->assertSame('super_admin', $employee->fresh()->user->access_level);
    }

    public function test_employee_create_form_can_be_opened_without_existing_employee(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $this->get(route('employees.create'))
            ->assertOk()
            ->assertSee('Documents')
            ->assertSee('CV Upload');
    }

    public function test_employee_can_be_archived_and_restored(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

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

    public function test_employee_profile_photo_can_be_uploaded(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $response = $this->post(route('employees.store'), [
            'first_name' => 'Meera',
            'last_name' => 'Shah',
            'email' => 'meera@example.test',
            'profile_photo_file' => UploadedFile::fake()->image('profile.jpg', 800, 800)->size(500),
        ]);

        $response->assertRedirect(route('employees.index'));

        $employee = Employee::where('email', 'meera@example.test')->firstOrFail();

        $this->assertStringStartsWith('/storage/employees/Meera-Shah-'.$employee->id.'/photo/profile-', $employee->profile_photo_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $employee->profile_photo_url));
    }

    public function test_employee_profile_photo_rejects_oversized_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $response = $this->post(route('employees.store'), [
            'first_name' => 'Nikhil',
            'email' => 'nikhil@example.test',
            'profile_photo_file' => UploadedFile::fake()->image('profile.jpg', 800, 800)->size(2200),
        ]);

        $response->assertInvalid('profile_photo_file');
    }

    public function test_employee_documents_can_be_uploaded_with_document_types(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $response = $this->post(route('employees.store'), [
            'first_name' => 'Arjun',
            'email' => 'arjun@example.test',
            'cv_file' => UploadedFile::fake()->create('resume.pdf', 250, 'application/pdf'),
            'related_document_types' => ['PAN Card', 'Aadhaar Card'],
            'related_documents' => [
                UploadedFile::fake()->create('pan.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('aadhaar.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('employees.index'));

        $employee = Employee::where('email', 'arjun@example.test')->firstOrFail();
        $this->assertNotNull($employee->cv_file_path);
        Storage::disk('public')->assertExists($employee->cv_file_path);

        $this->assertCount(2, $employee->related_document_paths);
        $this->assertSame('PAN Card', $employee->related_document_paths[0]['type']);
        $this->assertSame('Aadhaar Card', $employee->related_document_paths[1]['type']);
        Storage::disk('public')->assertExists($employee->related_document_paths[0]['path']);
        Storage::disk('public')->assertExists($employee->related_document_paths[1]['path']);
    }
}
