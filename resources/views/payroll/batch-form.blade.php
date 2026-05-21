@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Create Payslip Batch'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Payslips Batches / New</div>
<section class="loan-sheet">
    <form method="post" action="{{ route('payroll.batches.store') }}">
        @csrf
        <div class="loan-grid">
            <div class="loan-fields">
                <label>Batch Name</label><input name="name" required>
                <label>Date From</label><input type="date" name="date_from" required>
                <label>Date To</label><input type="date" name="date_to" required>
            </div>
            <div class="loan-fields">
                <label>Credit Note</label><input type="checkbox" name="credit_note" value="1">
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-oh">Save</button>
            <a href="{{ route('payroll.batches.index') }}" class="btn btn-oh-light">Cancel</a>
        </div>
    </form>
</section>
@endsection
