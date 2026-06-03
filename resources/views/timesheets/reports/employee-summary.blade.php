@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Employee Summary'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Reporting / Employee Summary</div>
    <div class="timesheet-controls">
        <a href="{{ route('timesheets.reports.employee') }}">Employee</a>
        <a href="{{ route('timesheets.reports.project') }}">Project</a>
        <a href="{{ route('timesheets.reports.task') }}">Task</a>
    </div>
    <table class="timesheet-table">
        <thead><tr><th>Employee</th><th>Department</th><th class="text-end">Entries</th><th class="text-end">Total Hours</th><th class="text-end">Approved Hours</th><th class="text-end">Billable Hours</th></tr></thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row->employee?->full_name }}</td>
                    <td>{{ $row->employee?->workInformation?->department?->name }}</td>
                    <td class="text-end">{{ $row->entries }}</td>
                    <td class="text-end">{{ number_format((float) $row->total_hours, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $row->approved_hours, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $row->billable_hours, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
