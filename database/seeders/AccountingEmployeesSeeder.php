<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWorkInformation;
use App\Models\JobPosition;
use Illuminate\Database\Seeder;

class AccountingEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(
            ['name' => 'Accounting'],
            ['is_active' => true]
        );

        $company = Company::firstOrCreate(
            ['name' => 'Concept Global Accounting'],
            ['is_active' => true]
        );

        $department->companies()->syncWithoutDetaching([$company->id]);

        $positions = collect(['Director', 'VP', 'Sr. Acc. Mgr', 'HR. Exc'])
            ->mapWithKeys(function (string $name) use ($department, $company): array {
                $position = JobPosition::firstOrCreate(
                    ['department_id' => $department->id, 'name' => $name],
                    ['is_active' => true]
                );

                $position->companies()->syncWithoutDetaching([$company->id]);

                return [$name => $position];
            });

        $employees = [
            ['Shambhu', 'Jha', 'shambhuj@conceptglobal.co.in', 'Director'],
            ['Priya', 'Shaw', 'priyas@conceptglobal.co.in', 'VP'],
            ['Ayan', 'Dey', 'ayand@conceptglobal.co.in', 'Sr. Acc. Mgr'],
            ['Avijit', 'Chowdhury', 'avijitc@conceptglobal.co.in', 'Sr. Acc. Mgr'],
            ['Priya', 'Dhara', 'priyad@conceptglobal.co.in', 'HR. Exc'],
            ['Manisha', 'Shaw', 'manishas@conceptglobal.co.in', null],
            ['Sreeparna', 'Bose', 'sreeparnab@conceptglobal.co.in', null],
            ['Sourav', 'Chakraborty', 'souravc@conceptglobal.co.in', null],
            ['Vivekananda', 'Roy', 'vivekanandar@conceptglobal.co.in', null],
            ['Swara Sree', 'Mishra', 'swaram@conceptglobal.co.in', null],
            ['Sujit', 'Giri', 'sujitg@conceptglobal.co.in', null],
            ['Sabyasachi', 'Halder', 'sabyasachih@conceptglobal.co.in', null],
            ['Parna', 'Saha', 'parnas@conceptglobal.co.in', null],
            ['Soma', 'Ganguly', 'somag@conceptglobal.co.in', null],
            ['Shreyosh', 'Mukherjee', 'shreyoshm@conceptglobal.co.in', null],
            ['Srikanta', 'Mukherjee', 'srikantam@conceptglobal.co.in', null],
            ['Shreya', 'Yadav', 'shreyay@conceptglobal.co.in', null],
            ['Sayan', 'Das', 'sayand@conceptglobal.co.in', null],
        ];

        foreach ($employees as [$firstName, $lastName, $email, $positionName]) {
            $employee = Employee::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'is_active' => true,
                    'card_color' => '#6f42c1',
                ]
            );

            EmployeeWorkInformation::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'company_id' => $company->id,
                    'department_id' => $department->id,
                    'job_position_id' => $positionName ? $positions[$positionName]->id : null,
                    'email' => $email,
                    'employment_type' => 'Permanent',
                    'is_active' => true,
                ]
            );
        }
    }
}
