<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_routes_are_hidden(): void
    {
        $this->get('/attendances/check')->assertNotFound();
        $this->post('/attendances/toggle')->assertNotFound();
        $this->get('/attendance/reporting')->assertNotFound();
    }

    public function test_leave_can_be_approved(): void
    {
        $this->actingAs(User::factory()->create(['access_level' => 'super_admin']));

        $employee = Employee::create([
            'first_name' => 'Ronnie',
            'last_name' => 'Hart',
            'email' => 'ronnie@example.test',
        ]);
        $company = Company::create(['name' => 'My Company']);
        $leaveType = LeaveType::create(['name' => 'Compensatory Days']);
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'company_id' => $company->id,
            'from_date' => now()->toDateString(),
            'to_date' => now()->addDay()->toDateString(),
            'duration_days' => 2,
            'duration_hours' => 16,
            'status' => 'to_approve',
        ]);

        $this->patch(route('leaves.requests.approve', $leave))->assertSessionHas('status', 'Leave request approved.');

        $this->assertSame('approved', $leave->fresh()->status);
    }
}
