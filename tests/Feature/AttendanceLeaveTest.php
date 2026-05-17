<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceRegularizationRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_toggle_closes_open_record(): void
    {
        $employee = Employee::create([
            'first_name' => 'Mitchell',
            'last_name' => 'Admin',
            'email' => 'mitchell@example.test',
        ]);

        AttendanceRecord::create([
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'check_in_at' => now()->subHour(),
            'status' => 'open',
        ]);

        $this->post(route('attendance.toggle'))->assertSessionHas('status', 'Checked out.');

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'status' => 'closed',
        ]);
    }

    public function test_regularization_and_leave_can_be_approved(): void
    {
        $employee = Employee::create([
            'first_name' => 'Ronnie',
            'last_name' => 'Hart',
            'email' => 'ronnie@example.test',
        ]);
        $regularization = AttendanceRegularizationRequest::create([
            'employee_id' => $employee->id,
            'category' => 'Onsite',
            'reason' => 'Going for onsite',
            'from_at' => now(),
            'to_at' => now()->addDay(),
            'status' => 'requested',
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

        $this->patch(route('attendance.regularization.approve', $regularization))->assertSessionHas('status', 'Regularization approved.');
        $this->patch(route('leaves.requests.approve', $leave))->assertSessionHas('status', 'Leave request approved.');

        $this->assertSame('approved', $regularization->fresh()->status);
        $this->assertSame('approved', $leave->fresh()->status);
    }
}
