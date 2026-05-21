@extends('layouts.app', ['heading' => 'Payroll', 'subheading' => 'Payslip Batch'])

@section('content')
    @include('payroll._nav')
    <div class="loan-title">Payslips Batches / {{ $batch->name }}</div>
    <div class="px-3 pb-2 d-flex align-items-center gap-2 flex-wrap">
        @if($batch->state !== 'closed')
            <form method="post" action="{{ route('payroll.batches.compute', $batch) }}">@csrf<button class="btn btn-oh btn-sm">Compute Payslips</button></form>
        @endif
        @if(in_array($batch->state, ['computed', 'approved']))
            <form method="post" action="{{ route('payroll.batches.approve', $batch) }}">@csrf<button class="btn btn-oh btn-sm">Approve Payslips</button></form>
        @endif
        @if($batch->state === 'approved')
            <form method="post" action="{{ route('payroll.batches.close', $batch) }}">@csrf<button class="btn btn-oh-light btn-sm">Close Batch</button></form>
        @endif
        <span class="ms-auto badge {{ $batch->state === 'approved' ? 'bg-success' : ($batch->state === 'computed' ? 'bg-info text-dark' : ($batch->state === 'closed' ? 'bg-dark' : 'bg-secondary')) }}">
            {{ strtoupper($batch->state) }}
        </span>
    </div>
    <section class="loan-sheet">
        <h2>{{ $batch->name }}</h2>
        <div class="loan-grid">
            <div class="loan-fields readonly">
                <label>Period</label><span>{{ $batch->date_from?->format('d/m/Y') }} - {{ $batch->date_to?->format('d/m/Y') }}</span>
            </div>
            <div class="loan-fields readonly">
                <label>Credit Note</label><span>{{ $batch->credit_note ? 'Yes' : 'No' }}</span>
            </div>
        </div>
        <table class="loan-table">
            <thead><tr><th>Reference</th><th>Employee</th><th>Payslip Name</th><th>Date From</th><th>Date To</th><th>Status</th><th>Download</th></tr></thead>
            <tbody>
            @foreach($batch->payslips as $p)
                <tr>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $p->employee?->full_name }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->date_from?->format('d/m/Y') }}</td>
                    <td>{{ $p->date_to?->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $p->status === 'approved' ? 'bg-success' : ($p->status === 'computed' ? 'bg-info text-dark' : ($p->status === 'done' ? 'bg-dark' : 'bg-secondary')) }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td><a href="{{ route('payroll.payslips.download', $p) }}" class="btn btn-oh-light btn-sm">Download</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
@endsection
