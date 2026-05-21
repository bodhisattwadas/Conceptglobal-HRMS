@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Payslip Batches'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Payslip Batches</div>
    <div class="px-3 pb-2 d-flex gap-2">
        <a href="{{ route('payroll.batches.create') }}" class="btn btn-oh">Create</a>
        <button class="btn btn-oh-light" type="button">Action</button>
    </div>
    <table class="loan-table">
        <thead><tr><th>Batch</th><th>Period</th><th>Payslips</th><th>Status</th><th>Open</th></tr></thead>
        <tbody>
        @foreach($batches as $b)
            <tr>
                <td><a href="{{ route('payroll.batches.show', $b) }}">{{ $b->name }}</a></td>
                <td>{{ $b->date_from?->format('d/m/Y') }} - {{ $b->date_to?->format('d/m/Y') }}</td>
                <td>{{ $b->payslips_count }}</td>
                <td>{{ ucfirst($b->state) }}</td>
                <td><a href="{{ route('payroll.batches.show', $b) }}" class="btn btn-oh-light btn-sm">View</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $batches->links() }}</div>
@endsection
