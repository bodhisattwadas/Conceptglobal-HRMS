@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Loans'])

@section('content')
    @include('loans._nav')
    <div class="loan-title">All Loans</div>
    <div class="loan-actions">
        <div class="d-flex gap-2">
            <a href="{{ route('loans.create') }}" class="odoo-primary">Create</a>
            <button type="submit" form="loan-bulk-delete-form" class="odoo-danger">Delete Selected</button>
        </div>
        <div class="loan-statebar">
            <a href="{{ route('loans.index') }}" class="{{ $status === '' ? 'active' : '' }}">All</a>
            <a href="{{ route('loans.index', ['status' => 'draft']) }}" class="{{ $status === 'draft' ? 'active' : '' }}">Draft</a>
            <a href="{{ route('loans.index', ['status' => 'submitted']) }}" class="{{ $status === 'submitted' ? 'active' : '' }}">Submitted</a>
            <a href="{{ route('loans.index', ['status' => 'approved']) }}" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ route('loans.index', ['status' => 'refused']) }}" class="{{ $status === 'refused' ? 'active' : '' }}">Refused</a>
            <a href="{{ route('loans.index', ['status' => 'cancelled']) }}" class="{{ $status === 'cancelled' ? 'active' : '' }}">Cancelled</a>
        </div>
    </div>
    <form id="loan-bulk-delete-form" method="post" action="{{ route('loans.bulk-delete') }}">
        @csrf
    </form>
    <table class="loan-table">
        <thead><tr><th>Loan #</th><th>Employee</th><th>Department</th><th>Amount</th><th>Currency</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($loans as $loan)
            <tr>
                <td><input type="checkbox" form="loan-bulk-delete-form" name="loan_ids[]" value="{{ $loan->id }}" class="loan-check"> <a href="{{ route('loans.show', $loan) }}">{{ $loan->loan_number }}</a></td>
                <td>{{ $loan->employee?->full_name }}</td>
                <td>{{ $loan->department?->name }}</td>
                <td class="amount">Rs. {{ number_format((float)$loan->loan_amount, 2) }}</td>
                <td>{{ $loan->currency_code }}</td>
                <td>
                    <span class="loan-status-badge {{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                </td>
                <td>{{ $loan->request_date?->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-4">No loans found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $loans->links() }}</div>
@endsection

@push('styles')
<style>
    .loan-title { color: #6e4c94; font-size: 30px; padding: 10px 16px; }
    .loan-actions { align-items: center; background: #fff; border-bottom: 1px solid #d8dde6; display: flex; justify-content: space-between; padding: 8px 16px; }
    .loan-statebar a { color: #6b7280; padding: 8px 12px; text-decoration: none; }
    .loan-statebar a.active { background: #7e57a3; color: #fff; }
    .loan-table { border-collapse: collapse; width: 100%; }
    .loan-table th, .loan-table td { border: 1px solid #dfe3e8; padding: 7px 9px; }
    .loan-table th { background: #e9ecef; text-align: left; }
    .loan-table .amount { text-align: right; }
    .odoo-primary { background: #7e57a3; border: 1px solid #7e57a3; color: #fff; padding: 7px 11px; text-decoration: none; }
    .odoo-danger { background: #dc2626; border: 1px solid #dc2626; color: #fff; padding: 7px 11px; }
    .loan-status-badge {
        border-radius: 3px;
        color: #fff;
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        text-transform: uppercase;
    }
    .loan-status-badge.draft { background: #7e57a3; }
    .loan-status-badge.submitted { background: #2563eb; }
    .loan-status-badge.approved { background: #16a34a; }
    .loan-status-badge.refused { background: #dc2626; }
    .loan-status-badge.cancelled { background: #6b7280; }
</style>
@endpush
