<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\AttendanceDevice;
use App\Models\AttendanceRecord;
use App\Models\AttendanceRegularizationRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWorkInformation;
use App\Models\JobPosition;
use App\Models\JobRole;
use App\Models\LeavePendingWork;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@horilla.test'],
            [
                'name' => 'Horilla Admin',
                'password' => Hash::make('password'),
            ]
        );

        $company = Company::query()->firstOrCreate(
            ['name' => 'Concept Global'],
            [
                'is_hq' => true,
                'address' => 'Head Office',
                'country' => 'India',
                'state' => 'Kerala',
                'city' => 'Kochi',
                'zip' => '682001',
                'date_format' => 'DD/MM/YYYY',
                'time_format' => 'hh:mm A',
                'created_by_id' => $user->id,
                'modified_by_id' => $user->id,
            ]
        );

        $department = Department::query()->firstOrCreate(
            ['name' => 'Human Resources'],
            [
                'created_by_id' => $user->id,
                'modified_by_id' => $user->id,
            ]
        );
        $department->companies()->syncWithoutDetaching([$company->id]);

        $position = JobPosition::query()->firstOrCreate(
            ['department_id' => $department->id, 'name' => 'HR Manager'],
            [
                'created_by_id' => $user->id,
                'modified_by_id' => $user->id,
            ]
        );
        $position->companies()->syncWithoutDetaching([$company->id]);

        $role = JobRole::query()->firstOrCreate(
            ['job_position_id' => $position->id, 'name' => 'People Operations Lead'],
            [
                'created_by_id' => $user->id,
                'modified_by_id' => $user->id,
            ]
        );
        $role->companies()->syncWithoutDetaching([$company->id]);

        $employee = Employee::query()->firstOrCreate(
            ['email' => 'priya@horilla.test'],
            [
                'user_id' => $user->id,
                'badge_id' => 'EMP-0001',
                'first_name' => 'Priya',
                'last_name' => 'Menon',
                'phone' => '+91 99999 00001',
                'gender' => 'female',
                'country' => 'India',
                'state' => 'Kerala',
                'city' => 'Kochi',
                'is_active' => true,
            ]
        );

        EmployeeWorkInformation::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'company_id' => $company->id,
                'department_id' => $department->id,
                'job_position_id' => $position->id,
                'job_role_id' => $role->id,
                'email' => 'priya.menon@conceptglobal.test',
                'date_joining' => now()->subMonths(8)->toDateString(),
                'employment_type' => 'Permanent',
                'created_by_id' => $user->id,
                'modified_by_id' => $user->id,
            ]
        );

        $device = AttendanceDevice::query()->firstOrCreate(
            ['machine_ip' => '192.168.2.64'],
            [
                'port' => 124,
                'company_id' => $company->id,
                'working_address' => $company->name,
            ]
        );

        AttendanceRecord::query()->firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => now()->toDateString()],
            [
                'check_in_at' => now()->subMinutes(33),
                'worked_minutes' => 33,
                'source' => 'portal',
                'status' => 'open',
            ]
        );

        AttendanceRegularizationRequest::query()->firstOrCreate(
            ['employee_id' => $employee->id, 'category' => 'Onsite'],
            [
                'reason' => 'Going for onsite',
                'from_at' => now()->subDays(2)->setTime(15, 47, 56),
                'to_at' => now()->addDay()->setTime(15, 47, 56),
                'status' => 'requested',
            ]
        );

        $leaveTypes = [
            ['name' => 'Paid Time Off', 'default_days' => 18],
            ['name' => 'Compensatory Days', 'default_days' => 6],
            ['name' => 'Sick Time Off', 'default_days' => 10],
            ['name' => 'Unpaid', 'default_days' => 0, 'is_paid' => false],
            ['name' => 'Parental Leaves', 'default_days' => 30],
            ['name' => 'Extra Hours', 'default_days' => 0],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::query()->firstOrCreate(
                ['name' => $type['name']],
                [
                    'approval' => 'Approved by Time Off Officer',
                    'default_days' => $type['default_days'],
                    'is_paid' => $type['is_paid'] ?? true,
                ]
            );
        }

        $compensatory = LeaveType::where('name', 'Compensatory Days')->first();
        $leaveRequest = LeaveRequest::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $compensatory->id,
                'from_date' => '2026-02-07',
            ],
            [
                'company_id' => $company->id,
                'to_date' => '2026-02-09',
                'duration_days' => 2.81,
                'duration_hours' => 22.50,
                'remaining_legal_leaves' => 39.00,
                'description' => 'Trip with Family',
                'status' => 'to_approve',
            ]
        );

        LeavePendingWork::query()->firstOrCreate(
            ['leave_request_id' => $leaveRequest->id, 'task' => 'Meeting Room Furnitures'],
            ['project' => 'Office Design', 'description' => 'Check Furnitures']
        );
        LeavePendingWork::query()->firstOrCreate(
            ['leave_request_id' => $leaveRequest->id, 'task' => 'Social network integration'],
            ['project' => 'Research & Development', 'description' => 'Complete the integration work']
        );

        LeaveSetting::query()->firstOrCreate([], [
            'leave_reminder_enabled' => true,
            'leave_reminder_days_before' => 3,
            'employee_shift_enabled' => true,
            'vacation_management_enabled' => true,
        ]);
    }
}
