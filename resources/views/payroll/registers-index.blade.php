@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Contribution Registers'])

@section('content')
@include('payroll._nav')
<div class="loan-title">Contribution Registers</div>
<section class="loan-sheet">
    <form method="post" action="{{ route('payroll.registers.store') }}" class="mb-3 d-flex gap-2">
        @csrf
        <input name="name" class="form-control" placeholder="Register name (PF / ESI / PT / TDS)" required>
        <input name="code" class="form-control" placeholder="Code">
        <button class="btn btn-oh">Create</button>
    </form>
    <table class="loan-table">
        <thead><tr><th>Name</th><th>Code</th><th>Active</th></tr></thead>
        <tbody>
        @foreach($registers as $r)
            <tr><td>{{ $r->name }}</td><td>{{ $r->code }}</td><td>{{ $r->active ? 'Yes' : 'No' }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $registers->links() }}</div>
</section>
@endsection
