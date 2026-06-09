<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $employee = $request->user()->employee()
            ->with('workInformation.department', 'assignedProjects')
            ->firstOrFail();

        $timesheets = Timesheet::desktopSynced()
            ->with('project')
            ->where('employee_id', $employee->id)
            ->latest('date')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('employee-portal.dashboard', [
            'employee' => $employee,
            'projects' => $employee->assignedProjects()->orderBy('name')->get(),
            'timesheets' => $timesheets,
            'totalHours' => $timesheets->sum(fn (Timesheet $row) => (float) $row->hours_spent),
        ]);
    }
}
