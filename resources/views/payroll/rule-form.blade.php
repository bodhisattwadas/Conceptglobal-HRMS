@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Salary Rule'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Salary Rules / {{ $rule ? $rule->name : 'New' }}</div>
<section class="loan-sheet">
    <form method="post" action="{{ $rule ? route('payroll.rules.update', $rule) : route('payroll.rules.store') }}">
        @csrf
        @if($rule) @method('put') @endif
        <div class="loan-grid">
            <div class="loan-fields">
                <label>Name</label><input name="name" value="{{ old('name', $rule?->name) }}" required>
                <label>Code</label><input name="code" value="{{ old('code', $rule?->code) }}">
                <label>Sequence</label><input type="number" name="sequence" value="{{ old('sequence', $rule?->sequence ?? 10) }}">
                <label>Condition Based On</label><input name="condition_based_on" value="{{ old('condition_based_on', $rule?->condition_based_on ?? 'Always True') }}">
                <label>Amount Type</label><input name="amount_type" value="{{ old('amount_type', $rule?->amount_type ?? 'Python Code') }}">
                <label>Contribution Register</label>
                <select name="contribution_register">
                    <option value="">Select</option>
                    @foreach($registers as $r)
                        <option value="{{ $r->name }}" @selected(old('contribution_register', $rule?->contribution_register) === $r->name)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="loan-fields">
                <label>Active</label><input type="checkbox" name="active" value="1" @checked(old('active', $rule?->active ?? true))>
                <label>Appears on Payslip</label><input type="checkbox" name="appears_on_payslip" value="1" @checked(old('appears_on_payslip', $rule?->appears_on_payslip ?? true))>
                <label>Python Code (Safe Expression Placeholder)</label><textarea name="python_code" rows="6">{{ old('python_code', $rule?->python_code) }}</textarea>
            </div>
        </div>
        <div class="mt-3"><button class="btn btn-oh">Save</button></div>
    </form>
</section>
@endsection
