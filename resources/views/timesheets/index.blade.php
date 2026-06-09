@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => 'All Timesheets'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">Timesheets / All Timesheets</div>
    <div class="timesheet-toolbar">
        <div class="d-flex gap-2">
            <a href="{{ route('timesheets.export.csv', request()->query()) }}" class="btn btn-oh-light btn-sm"><i class="bi bi-download"></i></a>
        </div>
        <form method="get" class="timesheet-search">
            <input name="search" value="{{ request('search') }}" placeholder="Search...">
            <select name="group_by" onchange="this.form.submit()">
                @foreach(['employee' => 'Employee', 'project' => 'Project', 'department' => 'Department', 'date' => 'Date', 'status' => 'Status'] as $value => $label)
                    <option value="{{ $value }}" @selected($groupBy === $value)>Group: {{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-oh-light btn-sm"><i class="bi bi-search"></i></button>
        </form>
        <div class="text-end small text-secondary">{{ $timesheets->count() }} records / {{ number_format($totalHours, 2) }} h</div>
    </div>
    <div class="timesheet-controls">
        <span><i class="bi bi-funnel-fill"></i> Filters</span>
        <span><i class="bi bi-list-ul"></i> Group By</span>
        <span><i class="bi bi-star-fill"></i> Favorites</span>
        <span class="ms-auto"><i class="bi bi-list"></i> <i class="bi bi-grid ms-2"></i> <i class="bi bi-bar-chart ms-2"></i></span>
    </div>

    <table class="timesheet-table">
        <thead>
            <tr>
                <th style="width: 110px;">Date</th>
                <th>Employee</th>
                <th>Project</th>
                <th>Description</th>
                <th style="width: 120px;" class="text-end">Hours Spent</th>
                <th style="width: 120px;" class="text-end">Time</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 95px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $groupName => $rows)
                <tr class="timesheet-group">
                    <td colspan="4">{{ $groupName }} ({{ $rows->count() }})</td>
                    <td class="timesheet-total">{{ number_format($rows->sum(fn($row) => (float) $row->hours_spent), 2) }} h</td>
                    <td class="timesheet-total">
                        @php
                            $groupSeconds = $rows->sum(fn($row) => (int) ($row->timer_elapsed_seconds ?? 0));
                        @endphp
                        {{ intdiv($groupSeconds, 3600) }}h {{ intdiv($groupSeconds % 3600, 60) }}m {{ $groupSeconds % 60 }}s
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->date?->format('d/m/Y') }}</td>
                        <td>{{ $row->employee?->full_name }}</td>
                        <td>{{ $row->project?->name }}</td>
                        <td><a href="{{ route('timesheets.show', $row) }}">{{ $row->description ?: 'Timesheet entry' }}</a></td>
                        <td class="text-end">{{ number_format((float) $row->hours_spent, 2) }}</td>
                        <td class="text-end">
                            @php $seconds = (int) ($row->timer_elapsed_seconds ?? 0); @endphp
                            {{ intdiv($seconds, 3600) }}h {{ intdiv($seconds % 3600, 60) }}m {{ $seconds % 60 }}s
                        </td>
                        <td><span class="ts-badge ts-{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                        <td>
                            <a href="{{ route('timesheets.show', $row) }}#status-log" class="btn btn-oh-light btn-sm" title="View status">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="9" class="text-center text-secondary py-4">No timesheets found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
