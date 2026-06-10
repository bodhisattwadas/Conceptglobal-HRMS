@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Project Summary'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Reporting / Project Summary</div>
    @php
        $sortDirectionFor = function (string $column) use ($sort, $direction): string {
            return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
        };
        $sortArrow = function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return '↕';
            }

            return $direction === 'asc' ? '↑' : '↓';
        };
    @endphp
    <div class="timesheet-controls timesheet-report-switch">
        <a href="{{ route('timesheets.reports.employee') }}" class="report-switch-btn">Employee</a>
        <a href="{{ route('timesheets.reports.project') }}" class="report-switch-btn is-active">Project</a>
    </div>
    <table class="timesheet-table">
        <thead>
            <tr>
                <th><a class="sortable-btn" href="{{ route('timesheets.reports.project', ['sort' => 'project', 'dir' => $sortDirectionFor('project')]) }}">Project <span>{{ $sortArrow('project') }}</span></a></th>
                <th><a class="sortable-btn" href="{{ route('timesheets.reports.project', ['sort' => 'status', 'dir' => $sortDirectionFor('status')]) }}">Status <span>{{ $sortArrow('status') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.project', ['sort' => 'planned_hours', 'dir' => $sortDirectionFor('planned_hours')]) }}">Planned Hours <span>{{ $sortArrow('planned_hours') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.project', ['sort' => 'spent_hours', 'dir' => $sortDirectionFor('spent_hours')]) }}">Spent Hours <span>{{ $sortArrow('spent_hours') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.project', ['sort' => 'remaining', 'dir' => $sortDirectionFor('remaining')]) }}">Remaining <span>{{ $sortArrow('remaining') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.project', ['sort' => 'extra', 'dir' => $sortDirectionFor('extra')]) }}">Extra <span>{{ $sortArrow('extra') }}</span></a></th>
            </tr>
        </thead>
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
