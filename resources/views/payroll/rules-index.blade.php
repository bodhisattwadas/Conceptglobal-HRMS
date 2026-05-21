@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Salary Rules'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Salary Rules</div>
    <div class="px-3 pb-2"><a href="{{ route('payroll.rules.create') }}" class="btn btn-oh">Create</a></div>
    <table class="loan-table">
        <thead><tr><th>Name</th><th>Code</th><th>Sequence</th><th>Amount Type</th><th>Active</th></tr></thead>
        <tbody>
        @foreach($rules as $r)
            <tr>
                <td><a href="{{ route('payroll.rules.show', $r) }}">{{ $r->name }}</a></td>
                <td>{{ $r->code }}</td>
                <td>{{ $r->sequence }}</td>
                <td>{{ $r->amount_type }}</td>
                <td>{{ $r->active ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $rules->links() }}</div>
@endsection
