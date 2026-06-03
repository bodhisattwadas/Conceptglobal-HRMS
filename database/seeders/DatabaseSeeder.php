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
use App\Models\PayrollContract;
use App\Models\PayrollContributionRegister;
use App\Models\PayrollPayslip;
use App\Models\PayrollPayslipBatch;
use App\Models\PayrollSalaryRule;
use App\Models\PayrollSalaryStructure;
use App\Models\PayrollSetting;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use App\Models\TimesheetSetting;
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
        $this->seedPayrollModuleDemoData($company);
        $this->seedTimesheetModuleDemoData($company);
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

        $loan = EmployeeLoan::withTrashed()->updateOrCreate(
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
        if ($loan->trashed()) {
            $loan->restore();
        }

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

        $loan2 = EmployeeLoan::withTrashed()->updateOrCreate(
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
                'currency_code' => 'INR',
                'status' => 'draft',
                'total_amount' => 3000.00,
                'total_paid_amount' => 0,
                'balance_amount' => 3000.00,
            ]
        );
        if ($loan2->trashed()) {
            $loan2->restore();
        }
    }

    private function seedPayrollModuleDemoData(Company $company): void
    {
        PayrollSalaryStructure::firstOrCreate(['reference' => 'BASE'], ['name' => 'Base for new structures', 'salary_rules_count' => 3]);
        PayrollSalaryStructure::firstOrCreate(['reference' => 'ME'], ['name' => 'Marketing Executive', 'salary_rules_count' => 4]);
        PayrollSalaryStructure::firstOrCreate(['reference' => 'MEGG'], ['name' => 'Marketing Executive for Gilles Grave', 'salary_rules_count' => 2]);

        foreach ([
            ['name' => 'Provident Fund', 'code' => 'PF'],
            ['name' => 'ESI', 'code' => 'ESI'],
            ['name' => 'Professional Tax', 'code' => 'PT'],
            ['name' => 'TDS', 'code' => 'TDS'],
            ['name' => 'Gratuity', 'code' => 'GRAT'],
        ] as $reg) {
            PayrollContributionRegister::firstOrCreate(['code' => $reg['code']], ['name' => $reg['name'], 'active' => true]);
        }

        PayrollSetting::firstOrCreate([], [
            'default_currency_code' => 'INR',
            'payroll_approval_required' => true,
            'include_attendance_in_payroll' => true,
            'include_leave_in_payroll' => true,
            'include_timesheet_in_payroll' => false,
            'default_working_days_per_month' => 26,
            'default_working_hours_per_day' => 8,
        ]);

        $rules = [
            ['Basic Salary', 'BASIC', 10, 'Always True', 'Fixed', 'result = contract.wage', null],
            ['House Rent Allowance', 'HRA', 20, 'Always True', 'Percentage', 'result = basic * 0.40', null],
            ['Medical Allowance', 'MED', 30, 'Always True', 'Fixed', 'result = 1250', null],
            ['Special Allowance', 'SPL', 40, 'Always True', 'Fixed', 'result = 3000', null],
            ['Provident Fund Deduction', 'PF_DED', 80, 'Always True', 'Percentage', 'result = basic * 0.12', 'Provident Fund'],
            ['Professional Tax', 'PT_DED', 85, 'Always True', 'Fixed', 'result = 200', 'Professional Tax'],
            ['TDS Deduction', 'TDS_DED', 90, 'Always True', 'Formula', 'result = gross * 0.05', 'TDS'],
            ['Net Salary', 'NET', 999, 'Always True', 'Formula', 'result = gross - deductions', null],
        ];
        foreach ($rules as [$name, $code, $seq, $cond, $type, $py, $reg]) {
            PayrollSalaryRule::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'sequence' => $seq,
                    'active' => true,
                    'appears_on_payslip' => true,
                    'condition_based_on' => $cond,
                    'amount_type' => $type,
                    'python_code' => $py,
                    'contribution_register' => $reg,
                ]
            );
        }

        $abigail = Employee::where('first_name', 'Abigail')->where('last_name', 'Peterson')->first();
        if (! $abigail) return;

        $contract = PayrollContract::firstOrCreate(
            ['contract_name' => 'abi contract', 'employee_id' => $abigail->id],
            [
                'department_id' => $abigail->workInformation?->department_id,
                'job_position_id' => $abigail->workInformation?->job_position_id,
                'start_date' => '2022-02-09',
                'end_date' => null,
                'notice_period_days' => 0,
                'employee_category' => 'Employee',
                'salary_structure' => 'Base for new structures',
                'salary_structure_type' => 'Employee',
                'working_schedule' => 'Standard 40 Hours/week/Monthly',
                'hr_responsible' => 'Mitchell Admin',
                'state' => 'running',
                'notes' => '',
            ]
        );

        $batch = PayrollPayslipBatch::firstOrCreate(
            ['name' => 'batch feb payroll'],
            ['date_from' => '2022-02-01', 'date_to' => '2022-02-28', 'credit_note' => false, 'state' => 'draft']
        );

        $rows = [
            ['SLIP0008', 'Salary Slip of Abigail Peterson for February-2022', '2022-02-01', '2022-02-28', 'draft', 'Abigail', 'Peterson'],
            ['SLIP0009', 'Salary Slip of Anita Oliver for February-2022', '2022-02-01', '2022-02-28', 'draft', 'Anita', 'Oliver'],
            ['SLIP0006', 'Salary Slip of Audrey Peterson for January-2022', '2022-01-01', '2022-01-31', 'draft', 'Audrey', 'Peterson'],
        ];
        foreach ($rows as [$ref, $name, $from, $to, $status, $fn, $ln]) {
            $emp = Employee::where('first_name', $fn)->where('last_name', $ln)->first();
            if (! $emp) continue;
            PayrollPayslip::firstOrCreate(
                ['reference' => $ref],
                ['payroll_payslip_batch_id' => $batch->id, 'employee_id' => $emp->id, 'name' => $name, 'date_from' => $from, 'date_to' => $to, 'status' => $status]
            );
        }
    }

    private function seedTimesheetModuleDemoData(Company $company): void
    {
        TimesheetSetting::firstOrCreate([], [
            'company_id' => $company->id,
            'allow_future_entries' => false,
            'future_entry_limit_days' => 0,
            'require_approval' => true,
            'minimum_hours_per_entry' => 0.25,
            'maximum_hours_per_day' => 12,
            'restrict_to_assigned_tasks' => false,
            'lock_after_payroll' => true,
        ]);

        $mitchell = Employee::where('first_name', 'Mitchell')->where('last_name', 'Admin')->first();
        $projects = [
            'Office Design' => Project::firstOrCreate(
                ['name' => 'Office Design'],
                ['company_id' => $company->id, 'code' => 'OFFICE', 'manager_employee_id' => $mitchell?->id, 'status' => 'active', 'start_date' => '2021-12-01']
            ),
            'Research & Development' => Project::firstOrCreate(
                ['name' => 'Research & Development'],
                ['company_id' => $company->id, 'code' => 'RND', 'manager_employee_id' => $mitchell?->id, 'status' => 'active', 'start_date' => '2021-12-01']
            ),
        ];

        $taskRows = [
            ['Office Design', 'Meeting Room Furnitures', 40, '2022-02-28', 'in_progress'],
            ['Office Design', 'Room 2: Decoration', 24, '2022-02-20', 'in_progress'],
            ['Office Design', 'Office planning', 16, '2022-02-15', 'new'],
            ['Research & Development', 'Unit Testing', 30, '2022-02-28', 'in_progress'],
            ['Research & Development', 'User interface improvements', 42, '2022-03-10', 'in_progress'],
            ['Research & Development', 'Social network integration', 36, '2022-03-15', 'new'],
            ['Research & Development', 'Document management', 28, '2022-03-20', 'new'],
        ];

        $tasks = [];
        foreach ($taskRows as [$projectName, $title, $planned, $deadline, $status]) {
            $tasks[$title] = ProjectTask::firstOrCreate(
                ['project_id' => $projects[$projectName]->id, 'title' => $title],
                [
                    'company_id' => $company->id,
                    'planned_hours' => $planned,
                    'remaining_hours' => $planned,
                    'deadline' => $deadline,
                    'status' => $status,
                    'priority' => 'normal',
                ]
            );
        }

        $employeeNames = ['Abigail Peterson', 'Anita Oliver', 'Audrey Peterson', 'Marc Demo', 'Walter Horton', 'Keith Byrd', 'Toni Jimenez', 'Tina Williamson'];
        $employees = Employee::query()
            ->whereIn('email', [
                'abigail.peterson39@example.com',
                'anita.oliver32@example.com',
                'audrey.peterson25@example.com',
                'mark.brown23@example.com',
                'walter.horton80@example.com',
                'keith.byrd52@example.com',
                'toni.jimenez23@example.com',
                'tina.williamson98@example.com',
            ])
            ->with('workInformation')
            ->get()
            ->keyBy('full_name');

        foreach ($tasks as $task) {
            foreach ($employees->take(3) as $employee) {
                $task->assignees()->syncWithoutDetaching([$employee->id => ['assigned_at' => now()]]);
            }
        }

        $entries = [
            ['Abigail Peterson', 'Research & Development', 'Unit Testing', '2022-02-10', 'Requirements analysis', 3.00, true, 'approved'],
            ['Abigail Peterson', 'Office Design', 'Room 2: Decoration', '2022-02-05', 'Requirements analysis', 2.00, true, 'approved'],
            ['Marc Demo', 'Office Design', 'Meeting Room Furnitures', '2021-12-29', 'Requirements analysis', 1.00, true, 'submitted'],
            ['Walter Horton', 'Office Design', 'Meeting Room Furnitures', '2021-12-30', 'Requirements analysis', 1.00, true, 'submitted'],
            ['Keith Byrd', 'Office Design', 'Meeting Room Furnitures', '2022-01-01', 'On Site Visit', 2.00, false, 'draft'],
            ['Toni Jimenez', 'Research & Development', 'Social network integration', '2022-02-12', 'API integration', 4.00, true, 'approved'],
            ['Tina Williamson', 'Research & Development', 'Document management', '2022-02-14', 'Document review', 2.50, false, 'draft'],
            ['Anita Oliver', 'Research & Development', 'User interface improvements', '2022-02-15', 'Frontend polish', 5.00, true, 'submitted'],
        ];

        foreach ($entries as [$employeeName, $projectName, $taskTitle, $date, $description, $hours, $billable, $status]) {
            $employee = $employees[$employeeName] ?? null;
            $task = $tasks[$taskTitle] ?? null;
            if (! $employee || ! $task) {
                continue;
            }

            Timesheet::firstOrCreate(
                ['employee_id' => $employee->id, 'project_task_id' => $task->id, 'date' => $date, 'description' => $description],
                [
                    'company_id' => $company->id,
                    'department_id' => $employee->workInformation?->department_id,
                    'project_id' => $projects[$projectName]->id,
                    'hours_spent' => $hours,
                    'is_billable' => $billable,
                    'status' => $status,
                    'submitted_at' => in_array($status, ['submitted', 'approved'], true) ? now() : null,
                    'approved_at' => $status === 'approved' ? now() : null,
                    'source' => 'manual',
                ]
            );
        }

        foreach ($tasks as $task) {
            $spent = (float) $task->timesheets()->whereIn('status', ['draft', 'submitted', 'approved'])->sum('hours_spent');
            $planned = (float) $task->planned_hours;
            $task->update([
                'spent_hours' => $spent,
                'remaining_hours' => max($planned - $spent, 0),
                'extra_hours' => max($spent - $planned, 0),
                'progress_percent' => $planned > 0 ? min(($spent / $planned) * 100, 100) : 0,
            ]);
        }
    }
}
