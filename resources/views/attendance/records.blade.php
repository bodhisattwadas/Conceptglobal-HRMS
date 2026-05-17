@extends('layouts.app', ['heading' => 'Attendances', 'subheading' => 'Attendance records'])

@section('content')
    @include('attendance._nav')

    <div class="oh-page-title">
        <h1>Attendances</h1>
        <form class="oh-searchbar">
            <input class="form-control form-control-sm" placeholder="Search...">
            <i class="bi bi-search"></i>
        </form>
    </div>
    <div class="oh-actions">
        <div class="d-flex gap-2">
            <a href="{{ route('attendance.check') }}" class="btn btn-oh">Check In / Check Out</a>
        </div>
        <div></div>
        <div class="text-end small text-secondary">{{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} / {{ $records->total() }}</div>
    </div>
    <table class="oh-list-table">
        <thead>
        <tr>
            <th>Employee</th>
            <th>Date</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Worked Hours</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($records as $record)
            <tr>
                <td>{{ $record->employee->full_name }}</td>
                <td>{{ $record->attendance_date->format('d/m/Y') }}</td>
                <td>{{ $record->check_in_at?->format('H:i') ?? '-' }}</td>
                <td>{{ $record->check_out_at?->format('H:i') ?? '-' }}</td>
                <td>{{ sprintf('%02d:%02d', intdiv($record->worked_minutes, 60), $record->worked_minutes % 60) }}</td>
                <td>{{ ucfirst($record->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $records->links() }}</div>
@endsection

@push('styles')
    <style>
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
