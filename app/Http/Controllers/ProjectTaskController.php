<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ProjectTask;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function show(ProjectTask $task): View
    {
        return view('projects.tasks.show', [
            'task' => $task->load('project', 'assignees', 'timesheets.employee'),
            'employees' => Employee::active()->orderBy('first_name')->get(),
        ]);
    }
}
