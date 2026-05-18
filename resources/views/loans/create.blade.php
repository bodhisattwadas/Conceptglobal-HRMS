@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Loan Request'])

@section('content')
    @include('loans._nav')
    <div class="loan-title">Request for Loan / New</div>
    <form method="post" action="{{ route('loans.store') }}" id="loan-create-form">
        @csrf
        <div class="loan-actions"><div></div><div></div><div></div></div>
        <div class="loan-toolbar">
            <div></div>
            <div class="loan-status-badge draft">Draft</div>
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
                        <label>Department</label><span class="muted" id="employee-department">Auto from employee</span>
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
                        <label>Date</label><input name="request_date" type="date" value="{{ now()->toDateString() }}" required>
                        <label>Job Position</label><span class="muted" id="employee-job-position">Auto from employee</span>
                        <label>Payment Start Date</label><input name="payment_start_date" type="date" value="{{ now()->toDateString() }}" required>
                        <label>Currency</label><input name="currency_code" value="INR" readonly>
                    </div>
                </div>
                <div class="loan-tab">Installments</div>
                <table class="loan-table">
                    <thead><tr><th>Payment Date</th><th class="amount">Amount</th></tr></thead>
                    <tbody id="installments-body"><tr><td colspan="2" class="add-line">Click Compute Installment</td></tr></tbody>
                </table>
                <div class="loan-totals">
                    <div>Total Amount: <b id="total-amount">₹ 0.00</b></div>
                    <div>Total Paid Amount: <b id="total-paid-amount">₹ 0.00</b></div>
                    <div>Balance Amount: <b id="balance-amount">₹ 0.00</b></div>
                </div>
                <div class="loan-notes-wrap">
                    <label class="loan-notes-label">Notes (Optional)</label>
                    <textarea name="notes" class="loan-notes" rows="4" placeholder="Add notes..."></textarea>
                </div>
                <div class="loan-action-row loan-action-row-bottom">
                    <button class="odoo-primary" type="button" id="compute-installments-btn">Compute Installment</button>
                    <button class="odoo-secondary" type="submit" name="action" value="draft" id="save-draft-btn">Save as Draft</button>
                    <button class="odoo-primary" type="submit" name="action" value="submit" id="submit-btn">Submit</button>
                    <a href="{{ route('loans.index') }}" class="odoo-secondary">Cancel</a>
                </div>
            </section>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .loan-title { color: #6e4c94; font-size: 36px; padding: 14px 16px 8px; }
        .loan-actions { background: #fff; border-bottom: 1px solid #d8dde6; display: grid; grid-template-columns: 1fr auto 1fr; padding: 0 16px 10px; }
        .loan-action-row { align-items: center; display: flex; flex-wrap: wrap; gap: 6px; }
        .loan-action-row-bottom { margin-top: 10px; }
        .loan-toolbar { align-items: center; background: #fff; border-bottom: 1px solid #d8dde6; display: flex; justify-content: space-between; padding: 6px 16px; }
        .loan-status-badge {
            background: #7e57a3;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            text-transform: uppercase;
        }
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
        .odoo-primary, .odoo-secondary { font-size: 13px; line-height: 1; min-height: 30px; padding: 6px 10px; white-space: nowrap; }
        .odoo-primary { background: #7e57a3; border: 1px solid #7e57a3; color: #fff; text-decoration: none; }
        .odoo-secondary { background: #fff; border: 1px solid #d8dde6; color: #111827; text-decoration: none; }
        .loan-notes-wrap { margin-top: 16px; }
        .loan-notes-label { display: block; font-weight: 700; margin-bottom: 6px; }
        .loan-notes { border: 1px solid #cfd4dc; padding: 8px; width: 100%; }
    </style>
@endpush

@push('scripts')
<script>
    const employeeMeta = @json($employeeMeta);
    const employeeSelect = document.querySelector('select[name="employee_id"]');
    const companySelect = document.querySelector('select[name="company_id"]');
    const deptEl = document.getElementById('employee-department');
    const jobEl = document.getElementById('employee-job-position');
    const computeBtn = document.getElementById('compute-installments-btn');
    const loanAmountEl = document.querySelector('input[name="loan_amount"]');
    const installmentCountEl = document.querySelector('input[name="number_of_installments"]');
    const paymentStartEl = document.querySelector('input[name="payment_start_date"]');
    const installmentsBody = document.getElementById('installments-body');
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const totalAmountEl = document.getElementById('total-amount');
    const totalPaidAmountEl = document.getElementById('total-paid-amount');
    const balanceAmountEl = document.getElementById('balance-amount');

    function formatINR(amount) {
        return `₹ ${Number(amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }

    function setEmployeeDetails() {
        const selectedId = employeeSelect.value;
        const meta = employeeMeta[selectedId] || {};
        deptEl.textContent = meta.department || 'Auto from employee';
        jobEl.textContent = meta.job_position || 'Auto from employee';
        if (meta.company_id) companySelect.value = String(meta.company_id);
    }

    function computeInstallments() {
        const amount = Number(loanAmountEl.value || 0);
        const count = Number(installmentCountEl.value || 0);
        const startDate = paymentStartEl.value;
        if (!amount || !count || !startDate) return;

        installmentsBody.innerHTML = '';
        let remaining = amount;
        const base = Math.round((amount / count) * 100) / 100;
        const start = new Date(startDate);

        for (let i = 0; i < count; i++) {
            const rowDate = new Date(start);
            rowDate.setMonth(rowDate.getMonth() + i);
            const installmentAmount = i === count - 1 ? Math.round(remaining * 100) / 100 : base;
            remaining = Math.round((remaining - installmentAmount) * 100) / 100;
            const d = String(rowDate.getDate()).padStart(2, '0');
            const m = String(rowDate.getMonth() + 1).padStart(2, '0');
            const y = rowDate.getFullYear();

            installmentsBody.insertAdjacentHTML('beforeend', `<tr><td>${d}/${m}/${y}</td><td class="amount">${formatINR(installmentAmount)}</td></tr>`);
        }

        totalAmountEl.textContent = formatINR(amount);
        totalPaidAmountEl.textContent = formatINR(0);
        balanceAmountEl.textContent = formatINR(amount);
        saveDraftBtn?.focus();
    }

    employeeSelect.addEventListener('change', setEmployeeDetails);
    computeBtn.addEventListener('click', computeInstallments);
    loanAmountEl.addEventListener('input', () => {
        const amount = Number(loanAmountEl.value || 0);
        totalAmountEl.textContent = formatINR(amount);
        totalPaidAmountEl.textContent = formatINR(0);
        balanceAmountEl.textContent = formatINR(amount);
    });
    setEmployeeDetails();
    loanAmountEl.dispatchEvent(new Event('input'));
</script>
@endpush
