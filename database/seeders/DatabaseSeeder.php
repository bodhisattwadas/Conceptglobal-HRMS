<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\AttendanceDevice;
use App\Models\AttendanceRecord;
use App\Models\AttendanceRegularizationRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
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

        $this->seedEmployeeCloneRoster($company, $user);

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

        $mitchellEmployee = Employee::where('first_name', 'Mitchell')->where('last_name', 'Admin')->first();
        $ronnieEmployee = Employee::where('first_name', 'Ronnie')->where('last_name', 'Hart')->first();

        if ($mitchellEmployee && $compensatory) {
            $mitchellLeave = LeaveRequest::query()->firstOrCreate(
                [
                    'employee_id' => $mitchellEmployee->id,
                    'leave_type_id' => $compensatory->id,
                    'from_date' => '2022-02-07',
                ],
                [
                    'company_id' => $company->id,
                    'to_date' => '2022-02-09',
                    'duration_days' => 2.81,
                    'duration_hours' => 22.50,
                    'remaining_legal_leaves' => 39.00,
                    'description' => 'Trip with Family',
                    'status' => 'to_approve',
                ]
            );

            LeavePendingWork::query()->firstOrCreate(
                ['leave_request_id' => $mitchellLeave->id, 'task' => 'Meeting Room Furnitures'],
                ['project' => 'Office Design', 'description' => 'Check Furnitures']
            );
            LeavePendingWork::query()->firstOrCreate(
                ['leave_request_id' => $mitchellLeave->id, 'task' => 'Social network integration'],
                ['project' => 'Research & Development', 'description' => 'Complete the integration work']
            );
        }

        if ($ronnieEmployee) {
            AttendanceRegularizationRequest::query()->firstOrCreate(
                ['employee_id' => $ronnieEmployee->id, 'category' => 'Onsight'],
                [
                    'reason' => 'Going for onsight',
                    'from_at' => '2021-02-23 15:47:56',
                    'to_at' => '2021-02-26 15:47:56',
                    'status' => 'requested',
                ]
            );
        }

        LeaveSetting::query()->firstOrCreate([], [
            'leave_reminder_enabled' => true,
            'leave_reminder_days_before' => 3,
            'employee_shift_enabled' => true,
            'vacation_management_enabled' => true,
        ]);

        $this->seedLoanModuleDemoData($company);
    }

    private function seedEmployeeCloneRoster(Company $company, User $user): void
    {
        $departments = collect([
            'Administration',
            'Management',
            'Professional Services',
            'Research & Development',
            'Sales',
        ])->mapWithKeys(function (string $name) use ($company, $user) {
            $department = Department::query()->firstOrCreate(
                ['name' => $name],
                ['created_by_id' => $user->id, 'modified_by_id' => $user->id]
            );
            $department->companies()->syncWithoutDetaching([$company->id]);

            return [$name => $department];
        });

        $positions = collect([
            'Consultant' => 'Professional Services',
            'Experienced Developer' => 'Research & Development',
            'Marketing and Community Manager' => 'Sales',
            'Chief Executive Officer' => 'Management',
            'Chief Medical Officer' => 'Management',
            'Odoo Developer' => 'Research & Development',
            'Manager' => 'Management',
            'Chief Technical Officer' => 'Research & Development',
            'Human Resources Manager' => 'Administration',
        ])->mapWithKeys(function (string $departmentName, string $name) use ($departments, $company, $user) {
            $position = JobPosition::query()->firstOrCreate(
                ['department_id' => $departments[$departmentName]->id, 'name' => $name],
                ['created_by_id' => $user->id, 'modified_by_id' => $user->id]
            );
            $position->companies()->syncWithoutDetaching([$company->id]);

            return [$name => $position];
        });

        $people = [
            ['Abigail', 'Peterson', 'Consultant', 'Professional Services', 'abigail.peterson39@example.com', '(482)-233-3393', 'Consultant', '#b97a86'],
            ['Anita', 'Oliver', 'Experienced Developer', 'Research & Development', 'anita.oliver32@example.com', '(538)-497-4804', 'Employee', '#cf5f75'],
            ['Audrey', 'Peterson', 'Consultant', 'Professional Services', 'audrey.peterson25@example.com', '(203)-276-7903', 'Employee', '#d7a368'],
            ['Beth', 'Evans', 'Experienced Developer', 'Research & Development', 'beth.evans77@example.com', '(754)-532-3841', 'Employee', '#d5bcc8'],
            ['Doris', 'Cole', 'Consultant', 'Professional Services', 'doris.cole31@example.com', '(883)-331-5378', 'Consultant', '#3e3e48'],
            ['Eli', 'Lambert', 'Marketing and Community Manager', 'Sales', 'eli.lambert22@example.com', '(644)-169-1352', 'Employee', '#8f675f'],
            ['Ernest', 'Reed', 'Consultant', 'Professional Services', 'ernest.reed47@example.com', '(944)-518-8232', 'Consultant', '#927866'],
            ['Jeffrey', 'Kelly', 'Marketing and Community Manager', 'Sales', 'jeffrey.kelly72@example.com', '(916)-264-7362', 'Employee', '#63483d'],
            ['Jennie', 'Fletcher', 'Experienced Developer', 'Research & Development', 'jennie.fletcher76@example.com', '(157)-363-8229', 'Employee', '#c6689a'],
            ['Joe', '', 'Chief Medical Officer', 'Management', 'joe@example.com', '(376)-3852-7863', 'Employee', '#be6d62'],
            ['Juliet', '', 'Odoo Developer', 'Research & Development', 'juliet123@example.com', '(956)-3852-7863', 'Employee', '#b5d7df'],
            ['Keith', 'Byrd', 'Experienced Developer', 'Research & Development', 'keith.byrd52@example.com', '(449)-505-5146', 'Employee', '#8b725b'],
            ['Marc', 'Demo', 'Experienced Developer', 'Research & Development', 'mark.brown23@example.com', '+3281813700', 'Employee', '#d78f67'],
            ['Mitchell', 'Admin', 'Chief Executive Officer', 'Management', 'aiden.hughes71@example.com', '(237)-125-2389', 'Trainer', '#7f675d'],
            ['Paul', 'Williams', 'Experienced Developer', 'Research & Development', 'paul.williams59@example.com', '(114)-262-1607', 'Employee', '#b7a291'],
            ['Rachel', 'Perry', 'Marketing and Community Manager', 'Sales', 'jod@odoo.com', '(206)-267-3735', 'Employee', '#8e7561'],
            ['Randall', 'Lewis', 'Experienced Developer', 'Research & Development', 'randall.lewis74@example.com', '(332)-775-6660', 'Employee', '#589069'],
            ['Roger', 'Scott', 'Manager', 'Management', 'Roger123@example.com', '+3282823500', 'Employee', '#7b8a60'],
            ['Ronnie', 'Hart', 'Chief Technical Officer', 'Research & Development', 'ronnie.hart87@example.com', '(376)-310-7863', 'Trainer', '#4c6f85'],
            ['Sharlene', 'Rhodes', 'Experienced Developer', 'Research & Development', 'sharlene.rhodes49@example.com', '(450)-719-4182', 'Employee', '#c5a3a0'],
            ['Tina', 'Williamson', 'Human Resources Manager', 'Administration', 'tina.williamson98@example.com', '(360)-694-7266', 'Employee', '#d16d86'],
            ['Toni', 'Jimenez', 'Consultant', 'Professional Services', 'toni.jimenez23@example.com', '(663)-707-8451', 'Consultant', '#c5a0a0'],
            ['Walter', 'Horton', 'Experienced Developer', 'Research & Development', 'walter.horton80@example.com', '(350)-912-1201', 'Employee', '#6b5049'],
            ['demo', '', 'hr', 'Administration', 'demo@example.com', '+1 (650) 555-0111', 'Employee', '#d7d7d7'],
        ];

        $mitchell = null;
        $createdEmployees = [];
        foreach ($people as $index => [$first, $last, $positionName, $departmentName, $email, $phone, $type, $color]) {
            $employee = Employee::query()->updateOrCreate(
                ['email' => $email],
                [
                    'badge_id' => 'EMP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'profile_photo_url' => 'https://ui-avatars.com/api/?name='.urlencode(trim($first.' '.$last)).'&background='.ltrim($color, '#').'&color=fff&size=128',
                    'card_color' => $color,
                    'first_name' => $first,
                    'last_name' => $last ?: null,
                    'phone' => $phone,
                    'gender' => in_array($first, ['Abigail', 'Anita', 'Audrey', 'Beth', 'Doris', 'Jennie', 'Juliet', 'Rachel', 'Sharlene', 'Tina', 'Toni'], true) ? 'female' : 'male',
                    'country' => 'United States',
                    'state' => 'CA',
                    'city' => 'San Francisco',
                    'is_active' => true,
                ]
            );

            if ($first === 'Mitchell') {
                $mitchell = $employee;
            }

            $createdEmployees[$employee->full_name] = $employee;

            EmployeeWorkInformation::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'company_id' => $company->id,
                    'department_id' => $departments[$departmentName]->id,
                    'job_position_id' => $positions[$positionName]->id ?? null,
                    'reporting_manager_id' => $first === 'Mitchell' ? null : $mitchell?->id,
                    'coach_id' => $mitchell?->id,
                    'email' => $email,
                    'work_mobile' => $phone,
                    'work_phone' => $phone,
                    'employment_type' => $type,
                    'work_location' => 'Building 1, Second Floor',
                    'working_hours' => 'Standard 40 Hours / Week',
                    'timezone' => 'Europe/Brussels',
                    'date_joining' => '2022-02-14',
                    'created_by_id' => $user->id,
                    'modified_by_id' => $user->id,
                ]
            );
        }

        if ($mitchell) {
            foreach ($createdEmployees as $name => $employee) {
                $managerId = $name === 'Mitchell Admin' ? null : $mitchell->id;
                if (in_array($name, ['Anita Oliver', 'Audrey Peterson'], true) && isset($createdEmployees['Abigail Peterson'])) {
                    $managerId = $createdEmployees['Abigail Peterson']->id;
                }

                $employee->workInformation()->update([
                    'reporting_manager_id' => $managerId,
                    'coach_id' => $mitchell->id,
                ]);
            }
        }
    }

    private function seedLoanModuleDemoData(Company $company): void
    {
        $jeffrey = Employee::where('first_name', 'Jeffrey')->where('last_name', 'Kelly')->first();
        if (! $jeffrey) {
            return;
        }

        $loan = EmployeeLoan::query()->firstOrCreate(
            ['loan_number' => 'LO/0001'],
            [
                'employee_id' => $jeffrey->id,
                'department_id' => $jeffrey->workInformation?->department_id,
                'job_position_id' => $jeffrey->workInformation?->job_position_id,
                'company_id' => $company->id,
                'request_date' => '2022-06-03',
                'loan_amount' => 6000.00,
                'number_of_installments' => 3,
                'payment_start_date' => '2022-03-31',
                'currency_code' => 'USD',
                'status' => 'submitted',
                'total_amount' => 6000.00,
                'total_paid_amount' => 0,
                'balance_amount' => 6000.00,
            ]
        );

        $rows = [
            ['2022-03-31', 2000.00],
            ['2022-04-30', 2000.00],
            ['2022-05-30', 2000.00],
        ];
        foreach ($rows as $i => [$date, $amount]) {
            EmployeeLoanInstallment::query()->firstOrCreate(
                ['employee_loan_id' => $loan->id, 'installment_no' => $i + 1],
                ['payment_date' => $date, 'amount' => $amount, 'paid_amount' => 0, 'remaining_amount' => $amount, 'status' => 'pending']
            );
        }

        EmployeeLoan::query()->firstOrCreate(
            ['loan_number' => 'LO/0002'],
            [
                'employee_id' => $jeffrey->id,
                'department_id' => $jeffrey->workInformation?->department_id,
                'job_position_id' => $jeffrey->workInformation?->job_position_id,
                'company_id' => $company->id,
                'request_date' => '2022-06-10',
                'loan_amount' => 3000.00,
                'number_of_installments' => 3,
                'payment_start_date' => '2022-06-30',
                'currency_code' => 'USD',
                'status' => 'draft',
                'total_amount' => 3000.00,
                'total_paid_amount' => 0,
                'balance_amount' => 3000.00,
            ]
        );
    }
}
