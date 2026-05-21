@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Loan Request'])

@section('content')
    @include('loans._nav')
    <div class="loan-title">Request for Loan / {{ $loan->loan_number }}</div>
    <div class="loan-actions">
        <div>
            <a href="{{ route('loans.edit', $loan) }}" class="odoo-secondary">Edit</a>
            <a href="{{ route('loans.create') }}" class="odoo-secondary">Create</a>
        </div>
        <button class="odoo-secondary"><i class="bi bi-gear-fill"></i> Action</button>
        <div class="text-end">{{ $loan->id }} / {{ \App\Models\EmployeeLoan::count() }}</div>
    </div>

    <div class="loan-toolbar">
        <div class="d-flex gap-1">
            <form method="post" action="{{ route('loans.compute-installments', $loan) }}">@csrf <button class="odoo-primary">Compute Installment</button></form>
            @if($loan->status === 'draft')
                <form method="post" action="{{ route('loans.submit', $loan) }}">@csrf <button class="odoo-primary">Submit</button></form>
                <form method="post" action="{{ route('loans.cancel', $loan) }}">@csrf <button class="odoo-secondary">Cancel</button></form>
            @elseif($loan->status === 'submitted')
                <form method="post" action="{{ route('loans.approve', $loan) }}" class="d-flex gap-1">
                    @csrf
                    <input name="treasury_account" value="Treasury Account" hidden>
                    <input name="loan_account" value="Loan Account" hidden>
                    <input name="journal" value="Loan Journal" hidden>
                    <button class="odoo-primary">Approve</button>
                </form>
                <form method="post" action="{{ route('loans.refuse', $loan) }}">@csrf <input name="refusal_reason" value="Refused" hidden><button class="odoo-secondary">Refuse</button></form>
            @endif
        </div>
        <div class="loan-status-badge {{ $loan->status }}">{{ ucfirst($loan->status) }}</div>
    </div>

    <div class="loan-pattern">
        <section class="loan-sheet">
            <h2>{{ $loan->loan_number }}</h2>
            <div class="loan-grid">
                <div class="loan-fields readonly">
                    <label>Employee</label><span>{{ $loan->employee?->full_name }}</span>
                    <label>Department</label><span>{{ $loan->department?->name }}</span>
                    <label>Loan Amount</label><span>{{ number_format((float) $loan->loan_amount, 2) }}</span>
                    @if($loan->status !== 'draft')
                        <label>Treasury Account</label><span>{{ $loan->treasury_account }}</span>
                    @endif
                    <label>No Of Installments</label><span>{{ $loan->number_of_installments }}</span>
                    <label>Company</label><span>{{ $loan->company?->name }}</span>
                </div>
                <div class="loan-fields readonly">
                    <label>Date</label><span>{{ $loan->request_date?->format('d/m/Y') }}</span>
                    <label>Job Position</label><span>{{ $loan->jobPosition?->name }}</span>
                    @if($loan->status !== 'draft')
                        <label>Loan Account</label><span>{{ $loan->loan_account }}</span>
                        <label>Journal</label><span>{{ $loan->journal }}</span>
                    @endif
                    <label>Payment Start Date</label><span>{{ $loan->payment_start_date?->format('d/m/Y') }}</span>
                    <label>Currency</label><span>{{ $loan->currency_code }}</span>
                </div>
            </div>

            <div class="loan-tab">Installments</div>
            <table class="loan-table">
                <thead><tr><th>Payment Date</th><th class="amount">Amount</th></tr></thead>
                <tbody>
                    @forelse($loan->installments as $ins)
                        <tr><td>{{ $ins->payment_date?->format('d/m/Y') }}</td><td class="amount">{{ number_format((float)$ins->amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="add-line">Add a line</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="loan-totals">
                <div>Total Amount: <b>₹ {{ number_format((float)$loan->total_amount, 2) }}</b></div>
                <div>Total Paid Amount: <b>₹ {{ number_format((float)$loan->total_paid_amount, 2) }}</b></div>
                <div>Balance Amount: <b>₹ {{ number_format((float)$loan->balance_amount, 2) }}</b></div>
            </div>
            @if($loan->notes)
                <div class="loan-audit">
                    <h4>Notes</h4>
                    <p>{{ $loan->notes }}</p>
                </div>
            @endif
            <div class="loan-audit">
                <h4>Audit Trail</h4>
                <ul>
                    @if($loan->submitted_at)
                        <li>Submitted by {{ $loan->submitted_by }} on {{ $loan->submitted_at->format('d/m/Y H:i') }} (IP: {{ $loan->submitted_ip }})</li>
                    @endif
                    @if($loan->approved_at)
                        <li>Approved by {{ $loan->approved_by }} on {{ $loan->approved_at->format('d/m/Y H:i') }} (IP: {{ $loan->approved_ip }})</li>
                    @endif
                    @if($loan->refused_at)
                        <li>Refused by {{ $loan->refused_by }} on {{ $loan->refused_at->format('d/m/Y H:i') }} (IP: {{ $loan->refused_ip }})</li>
                    @endif
                    @if($loan->cancelled_at)
                        <li>Cancelled by {{ $loan->cancelled_by }} on {{ $loan->cancelled_at->format('d/m/Y H:i') }} (IP: {{ $loan->cancelled_ip }})</li>
                    @endif
                    @if(!$loan->submitted_at && !$loan->approved_at && !$loan->refused_at && !$loan->cancelled_at)
                        <li>No audit events yet.</li>
                    @endif
                </ul>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .loan-title { color: #6e4c94; font-size: 36px; padding: 14px 16px 8px; }
        .loan-actions { align-items: center; background: #fff; border-bottom: 1px solid #d8dde6; display: grid; grid-template-columns: 1fr auto 1fr; padding: 0 16px 10px; }
        .loan-toolbar { align-items: center; background: #fff; border-bottom: 1px solid #d8dde6; display: flex; justify-content: space-between; padding: 6px 16px; }
        .loan-status-badge {
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            text-transform: uppercase;
        }
        .loan-status-badge.draft { background: #7e57a3; }
        .loan-status-badge.submitted { background: #2563eb; }
        .loan-status-badge.approved { background: #16a34a; }
        .loan-status-badge.refused { background: #dc2626; }
        .loan-status-badge.cancelled { background: #6b7280; }
        .loan-pattern { background-color: #f4f4f4; background-image: radial-gradient(#d3d3d3 .6px, transparent .6px); background-size: 3px 3px; min-height: calc(100vh - 220px); padding: 10px 0 20px; }
        .loan-sheet { background: #fff; border: 1px solid #c8ced8; margin: 0 auto; max-width: 1140px; min-height: 520px; padding: 26px 32px; }
        .loan-sheet h2 { font-size: 48px; font-weight: 700; margin-bottom: 28px; }
        .loan-grid { display: grid; gap: 36px; grid-template-columns: 1fr 1fr; }
        .loan-fields { display: grid; grid-template-columns: 160px 1fr; row-gap: 8px; }
        .loan-fields label { font-weight: 700; padding-top: 5px; }
        .loan-fields.readonly span { border-left: 1px solid #d9dde6; color: #4f2f86; min-height: 24px; padding-left: 8px; padding-top: 5px; }
        .loan-tab { border: 1px solid #d8dde6; border-bottom: 0; color: #6e36a2; display: inline-block; margin-top: 24px; padding: 8px 12px; }
        .loan-table { border-collapse: collapse; width: 100%; }
        .loan-table th, .loan-table td { border: 1px solid #dfe3e8; padding: 6px 8px; }
        .loan-table th { background: #e9ecef; text-align: left; }
        .loan-table .amount { text-align: right; width: 160px; }
        .loan-table .add-line { color: #6e36a2; }
        .loan-totals { margin-left: auto; margin-top: 26px; text-align: right; width: 300px; }
        .loan-totals div { font-size: 30px; margin-bottom: 8px; }
        .odoo-primary { background: #7e57a3; border: 1px solid #7e57a3; color: #fff; padding: 7px 11px; text-decoration: none; }
        .odoo-secondary { background: #fff; border: 1px solid #d8dde6; color: #111827; padding: 7px 11px; text-decoration: none; }
        .loan-audit { margin-top: 16px; }
        .loan-audit h4 { color: #344054; font-size: 16px; margin-bottom: 6px; }
        .loan-audit ul { margin: 0; padding-left: 18px; }
    </style>
@endpush
