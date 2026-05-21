@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Salary Structures'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Salary Structures</div>
    <div class="px-3 pb-2"><a href="{{ route('payroll.structures.create') }}" class="btn btn-oh">Create</a></div>
    <table class="loan-table">
        <thead><tr><th>Name</th><th>Reference</th><th>Salary Rules</th></tr></thead>
        <tbody>
        @foreach($structures as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->reference }}</td>
                <td>{{ $s->salary_rules_count }} records</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
