@extends('layouts.app', ['heading' => 'Attendances', 'subheading' => 'Reporting'])

@section('content')
    @include('attendance._nav')

    <div class="oh-page-title">
        <h1>Attendance Reporting</h1>
        <form class="oh-searchbar">
            <input class="form-control form-control-sm" placeholder="Search...">
            <i class="bi bi-search"></i>
        </form>
    </div>

    <div class="oh-actions">
        <div class="d-flex gap-2">
            <button class="btn btn-oh-light"><i class="bi bi-funnel"></i> Filters</button>
            <button class="btn btn-oh-light"><i class="bi bi-list-task"></i> Group By</button>
            <button class="btn btn-oh-light"><i class="bi bi-star"></i> Favorites</button>
        </div>
        <div></div>
        <div class="small text-secondary">Today</div>
    </div>

    <div class="report-wrap">
        <div class="report-cards">
            <article class="report-card"><span>Present Today</span><strong>{{ $presentToday }}</strong></article>
            <article class="report-card"><span>Open Check Ins</span><strong>{{ $openToday }}</strong></article>
            <article class="report-card"><span>Pending Regularizations</span><strong>{{ $regularizationPending }}</strong></article>
            <article class="report-card"><span>Total Worked Hours</span><strong>{{ $workedToday }}</strong></article>
        </div>

        <table class="oh-list-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Days Present</th>
                    <th>Total Worked Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $row)
                    <tr>
                        <td>{{ $row->employee?->full_name ?? '-' }}</td>
                        <td>{{ $row->employee?->workInformation?->department?->name ?? '-' }}</td>
                        <td>{{ $row->days_present }}</td>
                        <td>{{ sprintf('%02d:%02d', intdiv((int) $row->total_worked_minutes, 60), ((int) $row->total_worked_minutes) % 60) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary">No attendance data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
    <style>
        .content > section { padding-bottom: 0 !important; }
        .report-wrap { background: #f4f5f7; min-height: calc(100vh - 210px); padding: 14px 16px 24px; }
        .report-cards { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(180px, 1fr)); margin-bottom: 14px; }
        .report-card { background: #fff; border: 1px solid #d8dde6; padding: 14px; }
        .report-card span { color: #6b7280; display: block; font-size: 12px; margin-bottom: 6px; }
        .report-card strong { color: #1f2937; font-size: 24px; }
        @media (max-width: 900px) {
            .report-cards { grid-template-columns: 1fr 1fr; }
        }
    </style>
@endpush
