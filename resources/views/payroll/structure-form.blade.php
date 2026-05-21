@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Salary Structure'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Salary Structures / New</div>
<section class="loan-sheet">
    <form method="post" action="{{ route('payroll.structures.store') }}">
        @csrf
        <div class="loan-grid">
            <div class="loan-fields">
                <label>Name</label><input name="name" required>
                <label>Reference</label><input name="reference" required>
                <label>Salary Rules Count</label><input type="number" name="salary_rules_count" value="0">
            </div>
        </div>
        <div class="mt-3"><button class="btn btn-oh">Save</button></div>
    </form>
</section>
@endsection
