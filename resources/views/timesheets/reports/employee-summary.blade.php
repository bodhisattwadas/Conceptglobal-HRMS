@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'Employee Summary'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / Reporting / Employee Summary</div>
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
        <a href="{{ route('timesheets.reports.employee') }}" class="report-switch-btn is-active">Employee</a>
        <a href="{{ route('timesheets.reports.project') }}" class="report-switch-btn">Project</a>
    </div>
    <table class="timesheet-table">
        <thead>
            <tr>
                <th><a class="sortable-btn" href="{{ route('timesheets.reports.employee', ['sort' => 'employee', 'dir' => $sortDirectionFor('employee')]) }}">Employee <span>{{ $sortArrow('employee') }}</span></a></th>
                <th><a class="sortable-btn" href="{{ route('timesheets.reports.employee', ['sort' => 'department', 'dir' => $sortDirectionFor('department')]) }}">Department <span>{{ $sortArrow('department') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.employee', ['sort' => 'entries', 'dir' => $sortDirectionFor('entries')]) }}">Entries <span>{{ $sortArrow('entries') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.employee', ['sort' => 'total_hours', 'dir' => $sortDirectionFor('total_hours')]) }}">Total Hours <span>{{ $sortArrow('total_hours') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.employee', ['sort' => 'approved_hours', 'dir' => $sortDirectionFor('approved_hours')]) }}">Approved Hours <span>{{ $sortArrow('approved_hours') }}</span></a></th>
                <th class="text-end"><a class="sortable-btn justify-content-end" href="{{ route('timesheets.reports.employee', ['sort' => 'billable_hours', 'dir' => $sortDirectionFor('billable_hours')]) }}">Billable Hours <span>{{ $sortArrow('billable_hours') }}</span></a></th>
            </tr>
        </thead>
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
