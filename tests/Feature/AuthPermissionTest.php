<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->get(route('employee.dashboard'))->assertOk()->assertSee('Office Design')->assertSee('Edit Profile');
        $this->get(route('employees.index'))->assertForbidden();
    }

    public function test_employee_can_edit_own_profile(): void
    {
        $user = User::factory()->create(['access_level' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Anita',
            'last_name' => 'Oliver',
            'email' => 'anita@example.test',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->get(route('employee.profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Contact admin to change login email.');

        $this->put(route('employee.profile.update'), [
            'first_name' => 'Anita',
            'last_name' => 'Oliver',
            'phone' => '9999900000',
            'gender' => 'female',
            'city' => 'Kochi',
        ])->assertRedirect(route('employee.dashboard'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => '9999900000',
            'gender' => 'female',
            'city' => 'Kochi',
        ]);
    }

    public function test_employee_can_upload_own_profile_image_and_documents(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['access_level' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Anita',
            'last_name' => 'Oliver',
            'email' => 'anita@example.test',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $this->put(route('employee.profile.update'), [
            'first_name' => 'Anita',
            'last_name' => 'Oliver',
            'profile_photo_file' => UploadedFile::fake()->image('profile.jpg', 800, 800)->size(500),
            'cv_file' => UploadedFile::fake()->create('resume.pdf', 250, 'application/pdf'),
            'related_document_types' => ['PAN Card'],
            'related_documents' => [
                UploadedFile::fake()->create('pan.pdf', 100, 'application/pdf'),
            ],
        ])->assertRedirect(route('employee.dashboard'));

        $employee->refresh();

        $this->assertStringStartsWith('/storage/employees/Anita-Oliver-'.$employee->id.'/photo/profile-', $employee->profile_photo_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $employee->profile_photo_url));
        Storage::disk('public')->assertExists($employee->cv_file_path);
        $this->assertSame('PAN Card', $employee->related_document_paths[0]['type']);
        Storage::disk('public')->assertExists($employee->related_document_paths[0]['path']);
    }
}
