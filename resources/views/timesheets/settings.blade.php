@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Configuration'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Configuration</div>
    <form method="post" action="{{ route('timesheets.settings.update') }}">
        @csrf
        <div class="px-3 pb-2 d-flex gap-2">
            <button class="btn btn-oh btn-sm">Save</button>
            <a href="{{ route('timesheets.index') }}" class="btn btn-oh-light btn-sm">Discard</a>
        </div>
        <section class="timesheet-sheet">
            <div class="timesheet-form-grid">
                <div class="timesheet-field"><label>Allow Future Entries</label><select name="allow_future_entries"><option value="0" @selected(!$settings->allow_future_entries)>No</option><option value="1" @selected($settings->allow_future_entries)>Yes</option></select></div>
                <div class="timesheet-field"><label>Future Entry Limit Days</label><input type="number" name="future_entry_limit_days" value="{{ $settings->future_entry_limit_days }}"></div>
                <div class="timesheet-field"><label>Minimum Hours Per Entry</label><input type="number" step="0.25" name="minimum_hours_per_entry" value="{{ $settings->minimum_hours_per_entry }}"></div>
                <div class="timesheet-field"><label>Maximum Hours Per Day</label><input type="number" step="0.25" name="maximum_hours_per_day" value="{{ $settings->maximum_hours_per_day }}"></div>
                <div class="timesheet-field"><label>Require Approval</label><select name="require_approval"><option value="0" @selected(!$settings->require_approval)>No</option><option value="1" @selected($settings->require_approval)>Yes</option></select></div>
                <div class="timesheet-field"><label>Restrict To Assigned Projects</label><select name="restrict_to_assigned_tasks"><option value="0" @selected(!$settings->restrict_to_assigned_tasks)>No</option><option value="1" @selected($settings->restrict_to_assigned_tasks)>Yes</option></select></div>
                <div class="timesheet-field"><label>Edit After Submit</label><select name="allow_employee_edit_after_submit"><option value="0" @selected(!$settings->allow_employee_edit_after_submit)>No</option><option value="1" @selected($settings->allow_employee_edit_after_submit)>Yes</option></select></div>
                <div class="timesheet-field"><label>Delete After Submit</label><select name="allow_employee_delete_after_submit"><option value="0" @selected(!$settings->allow_employee_delete_after_submit)>No</option><option value="1" @selected($settings->allow_employee_delete_after_submit)>Yes</option></select></div>
                <div class="timesheet-field"><label>Lock After Payroll</label><select name="lock_after_payroll"><option value="0" @selected(!$settings->lock_after_payroll)>No</option><option value="1" @selected($settings->lock_after_payroll)>Yes</option></select></div>
            </div>
        </section>
    </form>

    <section class="timesheet-sheet">
        <h5 class="mb-3">Projects</h5>
        <form method="post" action="{{ route('timesheets.projects.store') }}" class="timesheet-form-grid mb-4">
            @csrf
            <div class="timesheet-field"><label>Project Name</label><input name="name" required></div>
            <div class="timesheet-field"><label>Code</label><input name="code"></div>
            <div class="timesheet-field">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="done">Done</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="timesheet-field">
                <label>Assign Employees</label>
                <select name="employee_ids[]" multiple size="5">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="timesheet-field" style="grid-column: 1 / -1;"><label>Description</label><textarea name="description" rows="3"></textarea></div>
            <div style="grid-column: 1 / -1;"><button class="btn btn-oh btn-sm">Create Project</button></div>
        </form>

        <table class="timesheet-table">
            <thead><tr><th>Project</th><th>Status</th><th>Assigned Employees</th></tr></thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>{{ $project->name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $project->status)) }}</td>
                        <td>
                            <form method="post" action="{{ route('timesheets.projects.assignments.update', $project) }}" class="d-flex gap-2 align-items-start">
                                @csrf
                                <select name="employee_ids[]" multiple size="4" class="w-100">
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected($project->assignees->contains($employee))>{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-oh-light btn-sm">Save Assignees</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-secondary">No projects created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
