@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Contracts'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Employee Contracts</div>
    <div class="px-3 pb-2 d-flex gap-2">
        <a href="{{ route('payroll.contracts.create') }}" class="btn btn-oh">Create</a>
        <button class="btn btn-oh-light" type="button">Action</button>
    </div>
    <table class="loan-table">
        <thead><tr><th>Contract</th><th>Employee</th><th>Department</th><th>Job Position</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($contracts as $c)
            <tr>
                <td><a href="{{ route('payroll.contracts.show', $c) }}">{{ $c->contract_name }}</a></td>
                <td>{{ $c->employee?->full_name }}</td>
                <td>{{ $c->department?->name }}</td>
                <td>{{ $c->jobPosition?->name }}</td>
                <td>{{ ucfirst($c->state) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $contracts->links() }}</div>
@endsection
