@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Salary Rule'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Salary Rules / {{ $rule->name }}</div>
    <section class="loan-sheet">
        <h2>{{ $rule->name }}</h2>
        <div class="loan-grid">
            <div class="loan-fields readonly">
                <label>Code</label><span>{{ $rule->code }}</span>
                <label>Active</label><span>{{ $rule->active ? 'Yes' : 'No' }}</span>
                <label>Condition Based on</label><span>{{ $rule->condition_based_on }}</span>
                <label>Amount Type</label><span>{{ $rule->amount_type }}</span>
                <label>Python Code</label><span>{{ $rule->python_code }}</span>
                <label>Contribution Register</label><span>{{ $rule->contribution_register }}</span>
            </div>
            <div class="loan-fields readonly">
                <label>Sequence</label><span>{{ $rule->sequence }}</span>
                <label>Appears on Payslip</label><span>{{ $rule->appears_on_payslip ? 'Yes' : 'No' }}</span>
            </div>
        </div>
    </section>
@endsection
