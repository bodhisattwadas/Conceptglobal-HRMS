@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Loan Request'])

@section('content')
    @include('employees._module_nav')
    <div class="loan-title">Request for Loan / New</div>
    <form method="post" action="{{ route('loans.store') }}">
        @csrf
        <div class="loan-actions">
            <div><button class="odoo-primary">Save</button><a href="{{ route('employees.index') }}" class="odoo-secondary">Discard</a></div>
            <div></div>
            <div></div>
        </div>
        <div class="loan-toolbar">
            <div>
                <button class="odoo-primary" type="submit" formaction="#" disabled>Compute Installment</button>
                <button class="odoo-primary" type="submit" formaction="#" disabled>Submit</button>
                <button class="odoo-secondary" type="submit" formaction="#" disabled>Cancel</button>
            </div>
            <div class="loan-statebar"><span class="active">Draft</span><span>Submitted</span><span>Approved</span></div>
        </div>
        <div class="loan-pattern">
            <section class="loan-sheet">
                <h2>/</h2>
                <div class="loan-grid">
                    <div class="loan-fields">
                        <label>Employee</label>
                        <select name="employee_id" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <label>Department</label><span class="muted">Auto from employee</span>
                        <label>Loan Amount</label><input name="loan_amount" type="number" step="0.01" value="6000.00" required>
                        <label>No Of Installments</label><input name="number_of_installments" type="number" min="1" value="3" required>
                        <label>Company</label>
                        <select name="company_id">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="loan-fields">
                        <label>Date</label><input name="request_date" type="date" value="2022-06-03" required>
                        <label>Job Position</label><span class="muted">Auto from employee</span>
                        <label>Payment Start Date</label><input name="payment_start_date" type="date" value="2022-06-03" required>
                        <label>Currency</label><input name="currency_code" value="{{ $defaultCurrencyCode }}" required>
                    </div>
                </div>
                <div class="loan-tab">Installments</div>
                <table class="loan-table">
                    <thead><tr><th>Payment Date</th><th class="amount">Amount</th></tr></thead>
                    <tbody><tr><td colspan="2" class="add-line">Add a line</td></tr></tbody>
                </table>
                <div class="loan-totals">
                    <div>Total Amount: <b>$ 0.00</b></div>
                    <div>Total Paid Amount: <b>$ 0.00</b></div>
                    <div>Balance Amount: <b>$ 0.00</b></div>
                </div>
            </section>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .loan-title { color: #6e4c94; font-size: 36px; padding: 14px 16px 8px; }
        .loan-actions { background: #fff; border-bottom: 1px solid #d8dde6; display: grid; grid-template-columns: 1fr auto 1fr; padding: 0 16px 10px; }
        .loan-toolbar { align-items: center; background: #fff; border-bottom: 1px solid #d8dde6; display: flex; justify-content: space-between; padding: 6px 16px; }
        .loan-statebar span { color: #6b7280; padding: 8px 14px; }
        .loan-statebar .active { background: #7e57a3; color: #fff; }
        .loan-pattern { background-color: #f4f4f4; background-image: radial-gradient(#d3d3d3 .6px, transparent .6px); background-size: 3px 3px; min-height: calc(100vh - 220px); padding: 10px 0 20px; }
        .loan-sheet { background: #fff; border: 1px solid #c8ced8; margin: 0 auto; max-width: 1140px; min-height: 520px; padding: 26px 32px; }
        .loan-sheet h2 { font-size: 40px; font-weight: 400; margin-bottom: 28px; }
        .loan-grid { display: grid; gap: 36px; grid-template-columns: 1fr 1fr; }
        .loan-fields { display: grid; grid-template-columns: 160px 1fr; row-gap: 8px; }
        .loan-fields label { font-weight: 700; padding-top: 5px; }
        .loan-fields input, .loan-fields select { border: 1px solid #cfd4dc; height: 30px; padding: 4px 6px; }
        .loan-fields .muted { border-left: 1px solid #d9dde6; color: #6e36a2; padding-left: 8px; padding-top: 5px; }
        .loan-tab { border: 1px solid #d8dde6; border-bottom: 0; color: #6e36a2; display: inline-block; margin-top: 24px; padding: 8px 12px; }
        .loan-table { border-collapse: collapse; width: 100%; }
        .loan-table th, .loan-table td { border: 1px solid #dfe3e8; padding: 6px 8px; }
        .loan-table th { background: #e9ecef; text-align: left; }
        .loan-table .amount { text-align: right; width: 160px; }
        .loan-table .add-line { color: #6e36a2; }
        .loan-totals { margin-left: auto; margin-top: 26px; text-align: right; width: 280px; }
        .loan-totals div { font-size: 30px; margin-bottom: 8px; }
        .odoo-primary { background: #7e57a3; border: 1px solid #7e57a3; color: #fff; padding: 7px 11px; text-decoration: none; }
        .odoo-secondary { background: #fff; border: 1px solid #d8dde6; color: #111827; padding: 7px 11px; text-decoration: none; }
    </style>
@endpush
