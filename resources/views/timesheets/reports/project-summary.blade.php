@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Project Summary'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Reporting / Project Summary</div>
    <div class="timesheet-controls">
        <a href="{{ route('timesheets.reports.employee') }}">Employee</a>
        <a href="{{ route('timesheets.reports.project') }}">Project</a>
        <a href="{{ route('timesheets.reports.task') }}">Task</a>
    </div>
    <table class="timesheet-table">
        <thead><tr><th>Project</th><th>Status</th><th class="text-end">Planned Hours</th><th class="text-end">Spent Hours</th><th class="text-end">Remaining</th><th class="text-end">Extra</th></tr></thead>
        <tbody>
            @foreach($projects as $project)
                @php
                    $planned = $project->tasks->sum(fn($task) => (float) $task->planned_hours);
                    $spent = (float) $project->timesheets_sum_hours_spent;
                @endphp
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ ucfirst($project->status) }}</td>
                    <td class="text-end">{{ number_format($planned, 2) }}</td>
                    <td class="text-end">{{ number_format($spent, 2) }}</td>
                    <td class="text-end">{{ number_format(max($planned - $spent, 0), 2) }}</td>
                    <td class="text-end">{{ number_format(max($spent - $planned, 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
