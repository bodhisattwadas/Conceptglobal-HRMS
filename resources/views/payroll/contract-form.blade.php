@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Create Contract'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Contracts / New</div>
<section class="loan-sheet">
    <form method="post" action="{{ route('payroll.contracts.store') }}">
        @csrf
        <div class="loan-grid">
            <div class="loan-fields">
                <label>Contract Name</label><input name="contract_name" required>
                <label>Employee</label>
                <select name="employee_id" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                    @endforeach
                </select>
                <label>Start Date</label><input type="date" name="start_date">
                <label>End Date</label><input type="date" name="end_date">
                <label>Notice Period (Days)</label><input type="number" min="0" name="notice_period_days" value="0">
            </div>
            <div class="loan-fields">
                <label>Employee Category</label><input name="employee_category" value="Employee">
                <label>Salary Structure</label><input name="salary_structure" value="Base for new structures">
                <label>Salary Structure Type</label><input name="salary_structure_type" value="Employee">
                <label>Working Schedule</label><input name="working_schedule" value="Standard 40 Hours/week/Monthly">
                <label>HR Responsible</label><input name="hr_responsible" value="Mitchell Admin">
            </div>
        </div>
        <div class="loan-tab">Contract Details</div>
        <textarea name="notes" rows="5" class="form-control"></textarea>
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-oh">Save</button>
            <a href="{{ route('payroll.contracts.index') }}" class="btn btn-oh-light">Cancel</a>
        </div>
    </form>
</section>
@endsection
