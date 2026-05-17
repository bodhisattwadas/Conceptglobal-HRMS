<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\JobRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        return view('organization.index', [
            'menu' => request()->string('menu', 'companies')->toString(),
            'companies' => Company::latest()->get(),
            'departments' => Department::with('companies')->latest()->get(),
            'jobPositions' => JobPosition::with('department', 'companies')->latest()->get(),
            'jobRoles' => JobRole::with('jobPosition.department', 'companies')->latest()->get(),
        ]);
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        Company::create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
        ]));

        return back()->with('status', 'Company created.');
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'company_ids' => ['array'],
            'company_ids.*' => ['exists:companies,id'],
        ]);

        $department = Department::create(['name' => $data['name']]);
        $department->companies()->sync($data['company_ids'] ?? []);

        return back()->with('status', 'Department created.');
    }

    public function storeJobPosition(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:100'],
            'company_ids' => ['array'],
            'company_ids.*' => ['exists:companies,id'],
        ]);

        $position = JobPosition::create($data);
        $position->companies()->sync($data['company_ids'] ?? []);

        return back()->with('status', 'Job position created.');
    }

    public function storeJobRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_position_id' => ['required', 'exists:job_positions,id'],
            'name' => ['required', 'string', 'max:100'],
            'company_ids' => ['array'],
            'company_ids.*' => ['exists:companies,id'],
        ]);

        $role = JobRole::create($data);
        $role->companies()->sync($data['company_ids'] ?? []);

        return back()->with('status', 'Job role created.');
    }
}
