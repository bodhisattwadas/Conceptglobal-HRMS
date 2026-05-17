<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeWorkInformation;
use App\Models\JobPosition;
use App\Models\JobRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::with([
            'workInformation.company',
            'workInformation.department',
            'workInformation.jobPosition',
            'workInformation.jobRole',
        ])
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('badge_id', 'like', "%{$search}%")
                        ->orWhereHas('workInformation.department', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('workInformation.jobPosition', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('company_id'), function ($query) use ($request): void {
                $query->whereHas('workInformation', function ($query) use ($request): void {
                    $query->where('company_id', $request->integer('company_id'));
                });
            })
            ->when($request->filled('department_id'), function ($query) use ($request): void {
                $query->whereHas('workInformation', function ($query) use ($request): void {
                    $query->where('department_id', $request->integer('department_id'));
                });
            })
            ->when($request->string('status')->toString() !== '', function ($query) use ($request): void {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->orderBy('first_name')
            ->paginate(24)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'companies' => Company::active()->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'departmentCounts' => Department::orderBy('name')
                ->get()
                ->mapWithKeys(fn (Department $department) => [
                    $department->id => Employee::whereHas('workInformation', fn ($query) => $query->where('department_id', $department->id))->count(),
                ]),
            'companyCounts' => Company::orderBy('name')
                ->get()
                ->mapWithKeys(fn (Company $company) => [
                    $company->id => Employee::whereHas('workInformation', fn ($query) => $query->where('company_id', $company->id))->count(),
                ]),
            'viewMode' => $request->string('view', 'kanban')->toString(),
            'employeeTotal' => Employee::count(),
            'activeTotal' => Employee::active()->count(),
        ]);
    }

    public function create(): View
    {
        return view('employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedEmployee($request);

        $employee = Employee::create($data['employee']);
        EmployeeWorkInformation::create([
            ...$data['work'],
            'employee_id' => $employee->id,
        ]);
        EmployeeBankDetail::create([
            ...$data['bank'],
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('employees.index')->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'workInformation.company',
            'workInformation.department',
            'workInformation.jobPosition',
            'workInformation.jobRole',
            'workInformation.reportingManager',
            'workInformation.coach',
            'bankDetail',
        ]);

        return view('employees.show', [
            'employee' => $employee,
            'smartButtons' => $this->smartButtons($employee),
            'orgReports' => Employee::with('workInformation.jobPosition')
                ->whereHas('workInformation', fn ($query) => $query->where('reporting_manager_id', $employee->id))
                ->orderBy('first_name')
                ->take(3)
                ->get(),
        ]);
    }

    public function createDocument(Employee $employee): View
    {
        $employee->load('workInformation.jobPosition');

        return view('employees.documents-create', [
            'employee' => $employee,
        ]);
    }

    public function storeDocument(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notification_type' => ['nullable', 'string', 'max:80'],
            'days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        return redirect()->route('employees.show', $employee)->with('status', 'Document draft saved.');
    }

    public function timesheets(Employee $employee): View
    {
        $employee->load('workInformation.jobPosition');

        return view('employees.timesheets', [
            'employee' => $employee,
            'timesheets' => [
                ['date' => '02/10/2022', 'project' => 'Research & Development', 'task' => 'Unit Testing', 'description' => 'Requirements analysis', 'hours' => '03:00'],
                ['date' => '02/05/2022', 'project' => 'Office Design', 'task' => 'Room 2: Decoration', 'description' => 'Requirements analysis', 'hours' => '02:00'],
            ],
            'totalHours' => '05:00',
        ]);
    }

    public function edit(Employee $employee): View
    {
        $employee->load('workInformation', 'bankDetail');

        return view('employees.edit', [
            ...$this->formData(),
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validatedEmployee($request, $employee);

        $employee->update($data['employee']);
        $employee->workInformation()->updateOrCreate(
            ['employee_id' => $employee->id],
            $data['work']
        );
        $employee->bankDetail()->updateOrCreate(
            ['employee_id' => $employee->id],
            $data['bank']
        );

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
    }

    public function archive(Employee $employee): RedirectResponse
    {
        $employee->update(['is_active' => false]);

        return back()->with('status', 'Employee archived.');
    }

    public function restore(Employee $employee): RedirectResponse
    {
        $employee->update(['is_active' => true]);

        return back()->with('status', 'Employee restored.');
    }

    private function formData(): array
    {
        return [
            'companies' => Company::active()->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'jobPositions' => JobPosition::active()->orderBy('name')->get(),
            'jobRoles' => JobRole::active()->orderBy('name')->get(),
            'managers' => Employee::where('is_active', true)->orderBy('first_name')->get(),
            'timezones' => [
                'Asia/Kolkata',
                'America/Los_Angeles',
                'America/New_York',
                'Europe/Brussels',
                'Europe/London',
                'UTC',
            ],
        ];
    }

    private function validatedEmployee(Request $request, ?Employee $employee = null): array
    {
        $employeeId = $employee?->id ?? 'NULL';

        $data = $request->validate([
            'badge_id' => ['nullable', 'string', 'max:50'],
            'profile_photo_url' => ['nullable', 'url', 'max:255'],
            'card_color' => ['nullable', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:200'],
            'last_name' => ['nullable', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:255', "unique:employees,email,{$employeeId}"],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'qualification' => ['nullable', 'string', 'max:100'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'marital_status' => ['nullable', 'in:single,married,divorced'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_position_id' => ['nullable', 'exists:job_positions,id'],
            'job_role_id' => ['nullable', 'exists:job_roles,id'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id'],
            'coach_id' => ['nullable', 'exists:employees,id'],
            'work_email' => ['nullable', 'email', 'max:255'],
            'work_mobile' => ['nullable', 'string', 'max:30'],
            'work_phone' => ['nullable', 'string', 'max:30'],
            'date_joining' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'work_location' => ['nullable', 'string', 'max:150'],
            'working_hours' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_holder_name' => ['nullable', 'string', 'max:160'],
            'ifsc_code' => ['nullable', 'string', 'max:40'],
            'branch' => ['nullable', 'string', 'max:120'],
        ]);

        return [
            'employee' => [
                'badge_id' => $data['badge_id'] ?? null,
                'profile_photo_url' => $data['profile_photo_url'] ?? null,
                'card_color' => $data['card_color'] ?? '#6f42c1',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'children_count' => $data['children_count'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'address' => $data['address'] ?? null,
                'country' => $data['country'] ?? null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'zip' => $data['zip'] ?? null,
            ],
            'work' => [
                'company_id' => $data['company_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'job_position_id' => $data['job_position_id'] ?? null,
                'job_role_id' => $data['job_role_id'] ?? null,
                'reporting_manager_id' => $data['reporting_manager_id'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
                'email' => $data['work_email'] ?? null,
                'work_mobile' => $data['work_mobile'] ?? null,
                'work_phone' => $data['work_phone'] ?? null,
                'date_joining' => $data['date_joining'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'work_location' => $data['work_location'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'timezone' => $data['timezone'] ?? null,
            ],
            'bank' => [
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'branch' => $data['branch'] ?? null,
            ],
        ];
    }

    private function smartButtons(Employee $employee): array
    {
        return [
            ['label' => 'Not Connected', 'value' => '', 'icon' => 'bi-circle-fill'],
            ['label' => 'Contracts', 'value' => 0, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Time Off', 'value' => '0/0 Days', 'icon' => 'bi-calendar2-week'],
            ['label' => 'Documents', 'value' => 0, 'icon' => 'bi-list-ol', 'url' => route('employees.documents.create', $employee)],
            ['label' => 'Payslips', 'value' => 2, 'icon' => 'bi-credit-card'],
            ['label' => 'Timesheets', 'value' => '', 'icon' => 'bi-calendar3', 'url' => route('employees.timesheets.index', $employee)],
            ['label' => 'Loans', 'value' => 0, 'icon' => 'bi-bank'],
        ];
    }

    public function view(Request $request, string $view): View
    {
        $employees = Employee::paginate(15);
        return view("hr.employees.index", [
            'employees' => $employees,
            'viewMode' => $view,
        ]);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $employeeIds = $request->input('employee_ids', []);

        if ($action === 'archive') {
            Employee::whereIn('id', $employeeIds)->update(['is_active' => false]);
        } elseif ($action === 'unarchive') {
            Employee::whereIn('id', $employeeIds)->update(['is_active' => true]);
        } elseif ($action === 'delete') {
            Employee::whereIn('id', $employeeIds)->delete();
        }

        return redirect()->route('employees.index')->with('status', 'Bulk action completed.');
    }
}
