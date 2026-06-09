<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        return view('dashboard', [
            'companyCount' => Company::count(),
            'departmentCount' => Department::count(),
            'employeeCount' => Employee::count(),
            'jobPositionCount' => JobPosition::count(),
            'recentEmployees' => Employee::with('workInformation.company', 'workInformation.department')
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
