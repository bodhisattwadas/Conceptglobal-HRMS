@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Timesheet'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / {{ $timesheet->employee?->full_name }} / {{ $timesheet->date?->format('d/m/Y') }}</div>
    <div class="px-3 pb-2 d-flex gap-2 align-items-center">
        <a href="{{ route('timesheets.edit', $timesheet) }}" class="btn btn-oh btn-sm">Edit</a>
        @if($timesheet->status === 'draft')
            <form method="post" action="{{ route('timesheets.submit', $timesheet) }}">@csrf<button class="btn btn-oh btn-sm">Submit</button></form>
        @endif
        @if($timesheet->status === 'submitted')
            <form method="post" action="{{ route('timesheets.approve', $timesheet) }}">@csrf<button class="btn btn-oh btn-sm">Approve</button></form>
            <form method="post" action="{{ route('timesheets.reject', $timesheet) }}">@csrf<button class="btn btn-oh-light btn-sm">Reject</button></form>
        @endif
        @if($timesheet->status !== 'approved')
            <form method="post" action="{{ route('timesheets.destroy', $timesheet) }}">@csrf @method('DELETE')<button class="btn btn-oh-light btn-sm">Delete</button></form>
        @endif
        <span class="ms-auto ts-badge ts-{{ $timesheet->status }}">{{ ucfirst($timesheet->status) }}</span>
    </div>
    <section class="timesheet-sheet">
        <h2 class="mb-4">{{ $timesheet->description ?: 'Timesheet Entry' }}</h2>
        <div class="timesheet-form-grid">
            <div class="timesheet-field"><label>Employee</label><div>{{ $timesheet->employee?->full_name }}</div></div>
            <div class="timesheet-field"><label>Date</label><div>{{ $timesheet->date?->format('d/m/Y') }}</div></div>
            <div class="timesheet-field"><label>Project</label><div>{{ $timesheet->project?->name }}</div></div>
            <div class="timesheet-field"><label>Department</label><div>{{ $timesheet->department?->name ?: '-' }}</div></div>
            <div class="timesheet-field"><label>Hours Spent</label><div>{{ number_format((float) $timesheet->hours_spent, 2) }} h</div></div>
            <div class="timesheet-field">
                <label>Total Time</label>
                <div>
                    @php
                        $timerSeconds = (int) ($timesheet->timer_elapsed_seconds ?? 0);
                        $timerHours = intdiv($timerSeconds, 3600);
                        $timerMinutes = intdiv($timerSeconds % 3600, 60);
                        $timerRestSeconds = $timerSeconds % 60;
                    @endphp
                    {{ $timerHours }} hr {{ $timerMinutes }} min {{ $timerRestSeconds }} sec
                </div>
            </div>
            <div class="timesheet-field"><label>Billable</label><div>{{ $timesheet->is_billable ? 'Yes' : 'No' }}</div></div>
            <div class="timesheet-field"><label>Source</label><div>{{ ucfirst(str_replace('_', ' ', $timesheet->source)) }}</div></div>
            <div class="timesheet-field"><label>Submitted Machine IP</label><div>{{ $timesheet->desktop_submitted_machine_ip ?: '-' }}</div></div>
            <div class="timesheet-field"><label>Submitted Machine MAC</label><div>{{ $timesheet->desktop_submitted_machine_mac ?: '-' }}</div></div>
        </div>

        <h5 class="mt-4">Work Timer Log</h5>
        <table class="timesheet-table">
            <thead><tr><th>Action</th><th>Time</th><th>Elapsed</th><th>Total</th><th>Note</th></tr></thead>
            <tbody>
                @forelse(($timesheet->timer_logs ?? []) as $log)
                    <tr>
                        <td>{{ $log['action'] ?? '' }}</td>
                        <td>{{ $log['time'] ?? '' }}</td>
                        <td>{{ $log['elapsed'] ?? '' }}</td>
                        <td>{{ $log['total'] ?? '' }}</td>
                        <td>{{ $log['note'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">No timer activity recorded.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h5 id="status-log" class="mt-4">Status Log</h5>
        <table class="timesheet-table">
            <thead><tr><th>Changed At</th><th>From</th><th>To</th><th>Reason</th></tr></thead>
            <tbody>
                @forelse($timesheet->logs as $log)
                    <tr><td>{{ $log->changed_at?->format('d/m/Y H:i') }}</td><td>{{ $log->old_status ?: '-' }}</td><td>{{ $log->new_status }}</td><td>{{ $log->reason }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-secondary">No status changes logged.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
