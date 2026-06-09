<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationJobPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_position_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $oldDepartment = Department::create(['name' => 'Accounting']);
        $newDepartment = Department::create(['name' => 'Operations']);
        $company = Company::create(['name' => 'Concept Global']);
        $position = JobPosition::create([
            'department_id' => $oldDepartment->id,
            'name' => 'VP',
        ]);

        $response = $this->put(route('organization.job-positions.update', $position), [
            'department_id' => $newDepartment->id,
            'name' => 'Director',
            'company_ids' => [$company->id],
        ]);

        $response->assertRedirect(route('organization.index', ['menu' => 'job-positions']));
        $this->assertDatabaseHas('job_positions', [
            'id' => $position->id,
            'department_id' => $newDepartment->id,
            'name' => 'Director',
        ]);
        $this->assertTrue($position->fresh()->companies->contains($company));
    }

    public function test_job_position_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $department = Department::create(['name' => 'Accounting']);
        $company = Company::create(['name' => 'Concept Global']);
        $position = JobPosition::create([
            'department_id' => $department->id,
            'name' => 'VP',
        ]);
        $position->companies()->attach($company);

        $response = $this->delete(route('organization.job-positions.destroy', $position));

        $response->assertRedirect(route('organization.index', ['menu' => 'job-positions']));
        $this->assertDatabaseMissing('job_positions', ['id' => $position->id]);
        $this->assertDatabaseMissing('company_job_position', ['job_position_id' => $position->id]);
    }
}
