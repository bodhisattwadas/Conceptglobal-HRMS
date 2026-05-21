@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Payroll Settings'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Payroll Settings (India)</div>
<section class="loan-sheet">
    <form method="post" action="{{ route('payroll.settings.update') }}">
        @csrf
        <div class="loan-grid">
            <div class="loan-fields">
                <label>Default Currency</label>
                <select name="default_currency_code">
                    @foreach(['INR','USD','EUR'] as $c)
                        <option value="{{ $c }}" @selected($settings->default_currency_code === $c)>{{ $c }}</option>
                    @endforeach
                </select>
                <label>Working Days / Month</label><input type="number" step="0.01" name="default_working_days_per_month" value="{{ $settings->default_working_days_per_month }}">
                <label>Working Hours / Day</label><input type="number" step="0.01" name="default_working_hours_per_day" value="{{ $settings->default_working_hours_per_day }}">
            </div>
            <div class="loan-fields">
                <label>Approval Required</label><input type="checkbox" name="payroll_approval_required" value="1" @checked($settings->payroll_approval_required)>
                <label>Include Attendance</label><input type="checkbox" name="include_attendance_in_payroll" value="1" @checked($settings->include_attendance_in_payroll)>
                <label>Include Leave</label><input type="checkbox" name="include_leave_in_payroll" value="1" @checked($settings->include_leave_in_payroll)>
                <label>Include Timesheet</label><input type="checkbox" name="include_timesheet_in_payroll" value="1" @checked($settings->include_timesheet_in_payroll)>
            </div>
        </div>
        <div class="mt-3"><button class="btn btn-oh">Save Settings</button></div>
    </form>
</section>
@endsection
