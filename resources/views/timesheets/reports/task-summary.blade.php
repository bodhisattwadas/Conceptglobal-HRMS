@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Task Summary'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Reporting / Task Summary</div>
    <div class="timesheet-controls">
        <a href="{{ route('timesheets.reports.employee') }}">Employee</a>
        <a href="{{ route('timesheets.reports.project') }}">Project</a>
        <a href="{{ route('timesheets.reports.task') }}">Task</a>
    </div>
    <table class="timesheet-table">
        <thead><tr><th>Task</th><th>Project</th><th>Assignees</th><th class="text-end">Planned</th><th class="text-end">Spent</th><th>Progress</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td><a href="{{ route('projects.tasks.show', $task) }}">{{ $task->title }}</a></td>
                    <td>{{ $task->project?->name }}</td>
                    <td>{{ $task->assignees->pluck('full_name')->join(', ') }}</td>
                    <td class="text-end">{{ number_format((float) $task->planned_hours, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $task->timesheets_sum_hours_spent, 2) }}</td>
                    <td><div class="progress-thin"><span style="width: {{ (float) $task->progress_percent }}%"></span></div></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
