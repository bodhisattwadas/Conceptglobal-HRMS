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
                <div class="timesheet-field"><label>Restrict To Assigned Tasks</label><select name="restrict_to_assigned_tasks"><option value="0" @selected(!$settings->restrict_to_assigned_tasks)>No</option><option value="1" @selected($settings->restrict_to_assigned_tasks)>Yes</option></select></div>
                <div class="timesheet-field"><label>Edit After Submit</label><select name="allow_employee_edit_after_submit"><option value="0" @selected(!$settings->allow_employee_edit_after_submit)>No</option><option value="1" @selected($settings->allow_employee_edit_after_submit)>Yes</option></select></div>
                <div class="timesheet-field"><label>Delete After Submit</label><select name="allow_employee_delete_after_submit"><option value="0" @selected(!$settings->allow_employee_delete_after_submit)>No</option><option value="1" @selected($settings->allow_employee_delete_after_submit)>Yes</option></select></div>
                <div class="timesheet-field"><label>Lock After Payroll</label><select name="lock_after_payroll"><option value="0" @selected(!$settings->lock_after_payroll)>No</option><option value="1" @selected($settings->lock_after_payroll)>Yes</option></select></div>
            </div>
        </section>
    </form>
</div>
@endsection
