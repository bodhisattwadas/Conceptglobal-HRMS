<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
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
