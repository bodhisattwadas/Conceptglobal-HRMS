@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Project Task'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Projects / {{ $task->project?->name }} / {{ $task->title }}</div>
    <div class="px-3 pb-2 d-flex gap-2 align-items-center">
        <span class="ms-auto ts-badge ts-submitted">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
    </div>
    <section class="timesheet-sheet">
        <h2>{{ $task->title }}</h2>
        <div class="timesheet-form-grid">
            <div class="timesheet-field"><label>Project</label><div>{{ $task->project?->name }}</div></div>
            <div class="timesheet-field"><label>Assignees</label><div>{{ $task->assignees->pluck('full_name')->join(', ') ?: '-' }}</div></div>
            <div class="timesheet-field"><label>Deadline</label><div>{{ $task->deadline?->format('d/m/Y') ?: '-' }}</div></div>
            <div class="timesheet-field"><label>Recurrent</label><div>{{ $task->is_recurrent ? 'Yes' : 'No' }}</div></div>
        </div>
        <div class="mt-4 border-bottom">
            <span class="d-inline-block bg-white border px-3 py-2 border-bottom-0">Timesheets</span>
        </div>
        <div class="d-flex align-items-center gap-4 py-3">
            <div><strong>Initially Planned Hours:</strong> {{ number_format((float) $task->planned_hours, 2) }}</div>
            <div class="progress-thin"><span style="width: {{ (float) $task->progress_percent }}%"></span></div>
            <div>{{ number_format((float) $task->progress_percent, 2) }}%</div>
            <div class="ms-auto"><strong>Remaining:</strong> {{ number_format((float) $task->remaining_hours, 2) }} h</div>
            <div><strong>Extra:</strong> {{ number_format((float) $task->extra_hours, 2) }} h</div>
        </div>
        <table class="timesheet-table">
            <thead><tr><th>Date</th><th>Employee</th><th>Description</th><th class="text-end">Hours Spent</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($task->timesheets as $row)
                    <tr>
                        <td>{{ $row->date?->format('d/m/Y') }}</td>
                        <td>{{ $row->employee?->full_name }}</td>
                        <td><a href="{{ route('timesheets.show', $row) }}">{{ $row->description }}</a></td>
                        <td class="text-end">{{ number_format((float) $row->hours_spent, 2) }}</td>
                        <td><span class="ts-badge ts-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">No timesheets recorded for this task.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
