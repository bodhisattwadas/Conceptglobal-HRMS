@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Contract'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Employees / {{ $contract->employee?->full_name }} / Contracts History / {{ $contract->contract_name }}</div>
    <section class="loan-sheet">
        <h2>{{ $contract->contract_name }}</h2>
        <div class="loan-grid">
            <div class="loan-fields readonly">
                <label>Employee</label><span>{{ $contract->employee?->full_name }}</span>
                <label>Contract Start Date</label><span>{{ $contract->start_date?->format('d/m/Y') }}</span>
                <label>Contract End Date</label><span>{{ $contract->end_date?->format('d/m/Y') }}</span>
                <label>Notice Period</label><span>{{ $contract->notice_period_days }} days</span>
                <label>Salary Structure Type</label><span>{{ $contract->salary_structure_type }}</span>
                <label>Working Schedule</label><span>{{ $contract->working_schedule }}</span>
            </div>
            <div class="loan-fields readonly">
                <label>Department</label><span>{{ $contract->department?->name }}</span>
                <label>Job Position</label><span>{{ $contract->jobPosition?->name }}</span>
                <label>Employee Category</label><span>{{ $contract->employee_category }}</span>
                <label>Salary Structure</label><span>{{ $contract->salary_structure }}</span>
                <label>Contract Type</label><span>{{ $contract->state }}</span>
                <label>HR Responsible</label><span>{{ $contract->hr_responsible }}</span>
            </div>
        </div>
        <div class="loan-tab">Contract Details</div>
        <p>{{ $contract->notes }}</p>
    </section>
@endsection
