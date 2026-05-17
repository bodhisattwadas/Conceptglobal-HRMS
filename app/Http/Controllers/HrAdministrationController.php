<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrAdministrationController extends Controller
{
    public function departments(Request $request): View
    {
        $departments = Department::query()
            ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderByRaw("FIELD(name, 'Administration', 'Management', 'Professional Services', 'Research & Development', 'Sales')")
            ->orderBy('name')
            ->get()
            ->map(function (Department $department): array {
                $employees = Employee::whereHas('workInformation', fn ($query) => $query->where('department_id', $department->id))->count();

                return [
                    'name' => $department->name,
                    'employees' => $employees,
                    'time_off' => $department->name === 'Research & Development' ? 1 : 0,
                    'allocation' => match ($department->name) {
                        'Administration' => 1,
                        'Management' => 3,
                        'Research & Development' => 1,
                        default => 0,
                    },
                    'applicants' => $department->name === 'Sales' ? 3 : 0,
                    'absence_current' => $department->name === 'Management' ? 1 : 0,
                    'absence_total' => max($employees, 1),
                ];
            });

        return view('hr-administration.departments', [
            'departments' => $departments,
        ]);
    }

    public function employees(Request $request): View
    {
        $employees = Employee::with(['workInformation.department', 'workInformation.jobPosition'])
            ->when($request->filled('department_id'), function ($query) use ($request): void {
                $query->whereHas('workInformation', fn ($query) => $query->where('department_id', $request->integer('department_id')));
            })
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(fn ($query) => $query
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('first_name')
            ->paginate(22)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('hr-administration.employees', [
            'employees' => $employees,
            'departments' => $departments,
            'departmentCounts' => $departments->mapWithKeys(fn (Department $department) => [
                $department->id => Employee::whereHas('workInformation', fn ($query) => $query->where('department_id', $department->id))->count(),
            ]),
        ]);
    }

    public function announcementCreate(): View
    {
        return view('hr-administration.announcement-create');
    }

    public function transferCreate(): View
    {
        return view('hr-administration.transfer-create', [
            'employees' => Employee::orderBy('first_name')->get(),
        ]);
    }

    public function legalCase(): View
    {
        return view('hr-administration.legal-case');
    }

    public function resignation(): View
    {
        return view('hr-administration.resignation');
    }

    public function custodyCreate(): View
    {
        return view('hr-administration.custody-create', [
            'employees' => Employee::orderBy('first_name')->get(),
        ]);
    }

    public function shiftWorkingTimes(): View
    {
        return view('hr-administration.shifts-working-times', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
