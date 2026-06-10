<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use App\Models\TimesheetSetting;
use App\Models\TimesheetStatusLog;
use App\Services\TimesheetProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TimesheetController extends Controller
{
    public function index(Request $request): View
    {
        $query = Timesheet::desktopSynced()
            ->with('employee.workInformation.department', 'project', 'task')
            ->latest('date')
            ->latest('id');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                    ->orWhereHas('project', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        foreach (['employee_id', 'project_id', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        $timesheets = $query->get();
        $groupBy = $request->input('group_by', 'employee');
        $groups = $this->groupTimesheets($timesheets, $groupBy);

        return view('timesheets.index', [
            'timesheets' => $timesheets,
            'groups' => $groups,
            'groupBy' => $groupBy,
            'employees' => Employee::active()->orderBy('first_name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'totalHours' => $timesheets->sum(fn (Timesheet $row) => (float) $row->hours_spent),
        ]);
    }

    public function show(Timesheet $timesheet): View
    {
        abort_unless($timesheet->source === 'desktop' && filled($timesheet->desktop_uuid), 404);

        return view('timesheets.show', [
            'timesheet' => $timesheet->load('employee.workInformation.department', 'project', 'task', 'logs'),
        ]);
    }

    public function edit(Timesheet $timesheet): View
    {
        return view('timesheets.form', $this->formData($timesheet));
    }

    public function update(Request $request, Timesheet $timesheet, TimesheetProgressService $progress): RedirectResponse
    {
        if ($timesheet->status === 'approved') {
            return back()->with('status', 'Approved timesheets are locked.');
        }

        $data = $this->validateTimesheet($request);
        $employee = Employee::with('workInformation')->findOrFail($data['employee_id']);
        $project = Project::findOrFail($data['project_id']);

        $timesheet->update($data + [
            'company_id' => $project->company_id,
            'department_id' => $employee->workInformation?->department_id,
        ]);

        $progress->recalculateTask($timesheet->task);

        return redirect()->route('timesheets.show', $timesheet)->with('status', 'Timesheet updated.');
    }

    public function destroy(Timesheet $timesheet, TimesheetProgressService $progress): RedirectResponse
    {
        if ($timesheet->status === 'approved') {
            return back()->with('status', 'Approved timesheets cannot be deleted.');
        }

        $task = $timesheet->task;
        $timesheet->delete();
        if ($task) {
            $progress->recalculateTask($task);
        }

        return redirect()->route('timesheets.index')->with('status', 'Timesheet deleted.');
    }

    public function submit(Timesheet $timesheet): RedirectResponse
    {
        return $this->transition($timesheet, 'submitted', ['submitted_at' => now(), 'submitted_by' => auth()->id()]);
    }

    public function approve(Timesheet $timesheet, TimesheetProgressService $progress): RedirectResponse
    {
        $response = $this->transition($timesheet, 'approved', ['approved_at' => now(), 'approved_by' => auth()->id()]);
        $progress->recalculateTask($timesheet->task);
        return $response;
    }

    public function reject(Request $request, Timesheet $timesheet, TimesheetProgressService $progress): RedirectResponse
    {
        $reason = $request->input('rejection_reason', 'Rejected by manager');
        $response = $this->transition($timesheet, 'rejected', [
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $reason,
        ], $reason);
        $progress->recalculateTask($timesheet->task);
        return $response;
    }

    public function employeeSummary(Request $request): View
    {
        $sort = $request->string('sort', 'employee')->toString();
        $direction = strtolower($request->string('dir', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';

        $rows = Timesheet::desktopSynced()
            ->with('employee.workInformation.department')
            ->select('employee_id')
            ->selectRaw('COUNT(*) as entries')
            ->selectRaw('SUM(hours_spent) as total_hours')
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN hours_spent ELSE 0 END) as approved_hours")
            ->selectRaw("SUM(CASE WHEN is_billable = 1 THEN hours_spent ELSE 0 END) as billable_hours")
            ->groupBy('employee_id')
            ->get();

        $sortable = ['employee', 'department', 'entries', 'total_hours', 'approved_hours', 'billable_hours'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'employee';

        $rows = $direction === 'desc'
            ? $rows->sortByDesc(fn ($row) => $this->employeeSortValue($row, $sort))->values()
            : $rows->sortBy(fn ($row) => $this->employeeSortValue($row, $sort))->values();

        return view('timesheets.reports.employee-summary', [
            'rows' => $rows,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function projectSummary(Request $request): View
    {
        $sort = $request->string('sort', 'project')->toString();
        $direction = strtolower($request->string('dir', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';

        $projects = Project::with('tasks')
            ->withSum(['timesheets' => fn ($query) => $query->desktopSynced()], 'hours_spent')
            ->orderBy('name')
            ->get();

        $sortable = ['project', 'status', 'planned_hours', 'spent_hours', 'remaining', 'extra'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'project';

        $projects = $direction === 'desc'
            ? $projects->sortByDesc(fn ($project) => $this->projectSortValue($project, $sort))->values()
            : $projects->sortBy(fn ($project) => $this->projectSortValue($project, $sort))->values();

        return view('timesheets.reports.project-summary', [
            'projects' => $projects,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function taskSummary(): View
    {
        return view('timesheets.reports.task-summary', [
            'tasks' => ProjectTask::with('project', 'assignees')
                ->withSum(['timesheets' => fn ($query) => $query->desktopSynced()], 'hours_spent')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function settings(): View
    {
        return view('timesheets.settings', [
            'settings' => TimesheetSetting::firstOrCreate([]),
            'projects' => Project::with('assignees')->orderBy('name')->get(),
            'employees' => Employee::active()->orderBy('first_name')->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'future_entry_limit_days' => ['required', 'integer', 'min:0'],
            'minimum_hours_per_entry' => ['required', 'numeric', 'min:0.01'],
            'maximum_hours_per_day' => ['required', 'numeric', 'min:1', 'max:24'],
            'desktop_timer_timeout_seconds' => ['required', 'integer', 'min:1', 'max:86400'],
        ]);

        TimesheetSetting::firstOrCreate([])->update($data + [
            'allow_future_entries' => $request->boolean('allow_future_entries'),
            'allow_employee_edit_after_submit' => $request->boolean('allow_employee_edit_after_submit'),
            'allow_employee_delete_after_submit' => $request->boolean('allow_employee_delete_after_submit'),
            'require_approval' => $request->boolean('require_approval'),
            'restrict_to_assigned_tasks' => $request->boolean('restrict_to_assigned_tasks'),
            'lock_after_payroll' => $request->boolean('lock_after_payroll'),
        ]);

        return back()->with('status', 'Timesheet settings saved.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,on_hold,done,cancelled'],
            'description' => ['nullable', 'string', 'max:2000'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $project = Project::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ]);

        $project->assignees()->sync($this->assignmentPayload($data['employee_ids'] ?? []));

        return back()->with('status', 'Project created.');
    }

    public function updateProjectAssignments(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $project->assignees()->sync($this->assignmentPayload($data['employee_ids'] ?? []));

        return back()->with('status', 'Project assignments updated.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = Timesheet::desktopSynced()
            ->with('employee.workInformation.department', 'project')
            ->orderBy('date')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Employee', 'Department', 'Project', 'Description', 'Hours Spent', 'Status', 'Billable']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->date?->format('d/m/Y'),
                    $row->employee?->full_name,
                    $row->department?->name,
                    $row->project?->name,
                    $row->description,
                    number_format((float) $row->hours_spent, 2),
                    ucfirst($row->status),
                    $row->is_billable ? 'Yes' : 'No',
                ]);
            }
            fclose($out);
        }, 'timesheets.csv', ['Content-Type' => 'text/csv']);
    }

    private function formData(?Timesheet $timesheet): array
    {
        return [
            'timesheet' => $timesheet,
            'employees' => Employee::active()->orderBy('first_name')->get(),
            'projects' => Project::orderBy('name')->get(),
        ];
    }

    private function validateTimesheet(Request $request): array
    {
        $settings = TimesheetSetting::firstOrCreate([]);
        $maxDate = $settings->allow_future_entries
            ? now()->addDays((int) $settings->future_entry_limit_days)->toDateString()
            : now()->toDateString();

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'date' => ['required', 'date', 'before_or_equal:'.$maxDate],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'hours_spent' => ['required', 'numeric', 'min:'.(float) $settings->minimum_hours_per_entry, 'max:'.(float) $settings->maximum_hours_per_day],
            'timer_elapsed_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'timer_logs' => ['nullable', 'json'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_billable' => ['nullable'],
        ]);

        $taskBelongsToProject = empty($data['project_task_id'])
            || ProjectTask::whereKey($data['project_task_id'])->where('project_id', $data['project_id'])->exists();
        if (! $taskBelongsToProject) {
            throw ValidationException::withMessages([
                'project_task_id' => 'Selected task does not belong to selected project.',
            ]);
        }

        return $data + [
            'is_billable' => $request->boolean('is_billable'),
            'timer_elapsed_seconds' => (int) $request->input('timer_elapsed_seconds', 0),
            'timer_logs' => json_decode($request->input('timer_logs', '[]'), true) ?: [],
        ];
    }

    private function transition(Timesheet $timesheet, string $status, array $fields = [], ?string $reason = null): RedirectResponse
    {
        $oldStatus = $timesheet->status;
        $timesheet->update($fields + ['status' => $status]);
        $this->logStatus($timesheet, $oldStatus, $status, $reason);
        return back()->with('status', 'Timesheet marked '.ucfirst($status).'.');
    }

    private function logStatus(Timesheet $timesheet, ?string $oldStatus, string $newStatus, ?string $reason = null): void
    {
        TimesheetStatusLog::create([
            'timesheet_id' => $timesheet->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'reason' => $reason,
            'changed_at' => now(),
        ]);
    }

    private function groupTimesheets($timesheets, string $groupBy)
    {
        return $timesheets->groupBy(function (Timesheet $row) use ($groupBy): string {
            return match ($groupBy) {
                'project' => $row->project?->name ?? 'No Project',
                'department' => $row->department?->name ?? 'No Department',
                'date' => $row->date?->format('d/m/Y') ?? 'No Date',
                'status' => ucfirst($row->status),
                default => $row->employee?->full_name ?? 'No Employee',
            };
        });
    }

    private function employeeSortValue($row, string $sort): string|float|int
    {
        return match ($sort) {
            'department' => strtolower((string) ($row->employee?->workInformation?->department?->name ?? '')),
            'entries' => (int) $row->entries,
            'total_hours' => (float) $row->total_hours,
            'approved_hours' => (float) $row->approved_hours,
            'billable_hours' => (float) $row->billable_hours,
            default => strtolower((string) ($row->employee?->full_name ?? '')),
        };
    }

    private function projectSortValue(Project $project, string $sort): string|float
    {
        $planned = (float) $project->tasks->sum(fn ($task) => (float) $task->planned_hours);
        $spent = (float) $project->timesheets_sum_hours_spent;

        return match ($sort) {
            'status' => strtolower((string) $project->status),
            'planned_hours' => $planned,
            'spent_hours' => $spent,
            'remaining' => max($planned - $spent, 0),
            'extra' => max($spent - $planned, 0),
            default => strtolower((string) $project->name),
        };
    }

    private function assignmentPayload(array $employeeIds): array
    {
        return collect($employeeIds)
            ->filter()
            ->mapWithKeys(fn ($employeeId): array => [(int) $employeeId => ['assigned_at' => now()]])
            ->all();
    }
}
