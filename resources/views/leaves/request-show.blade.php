@extends('layouts.app', ['heading' => 'Leaves', 'subheading' => 'My Time Off'])


@section('content')
    @include('leaves._nav', ['appTitle' => 'Leaves'])

    <div class="oh-page-title">
        <h1>My Time Off / {{ $leaveRequest->employee->full_name }} on {{ $leaveRequest->leaveType->name }}: {{ $leaveRequest->duration_hours }} hours on {{ $leaveRequest->from_date->format('Y-m-d') }}</h1>
        <div class="small text-secondary">2 / 2 &nbsp; <i class="bi bi-chevron-left"></i> &nbsp; <i class="bi bi-chevron-right"></i></div>
    </div>
    <div class="oh-actions">
        <div class="d-flex gap-2">
            <button class="btn btn-oh">Edit</button>
            <a href="{{ route('leaves.requests.create') }}" class="btn btn-oh-light">Create</a>
        </div>
        <button class="btn btn-oh-light"><i class="bi bi-gear-fill"></i> Action</button>
        <div></div>
    </div>
    <div class="px-3 py-1 bg-white border-bottom d-flex justify-content-between">
        <div class="d-flex gap-1">
            <form method="post" action="{{ route('leaves.requests.approve', $leaveRequest) }}">
                @csrf
                @method('patch')
                <button class="btn btn-oh btn-sm">Approve</button>
            </form>
            <form method="post" action="{{ route('leaves.requests.refuse', $leaveRequest) }}">
                @csrf
                @method('patch')
                <button class="btn btn-oh-light btn-sm">Refuse</button>
            </form>
            <form method="post" action="{{ route('leaves.requests.draft', $leaveRequest) }}">
                @csrf
                @method('patch')
                <button class="btn btn-oh-light btn-sm">Mark as Draft</button>
            </form>
        </div>
        <div class="oh-statusbar">
            @foreach (['to_approve' => 'To Approve', 'approved' => 'Approved'] as $state => $label)
                <span @class(['oh-state', 'active' => $leaveRequest->status === $state])>{{ $label }}</span>
            @endforeach
        </div>
    </div>
    <div class="oh-pattern">
        <div class="oh-sheet">
            <div class="oh-row">
                <div class="oh-label">Time Off Type</div>
                <div class="oh-value">{{ $leaveRequest->leaveType->name }}</div>
                <div class="oh-label">Remaining Legal Leaves</div>
                <div class="oh-value">{{ $leaveRequest->remaining_legal_leaves }}</div>
                <div class="oh-label">Dates</div>
                <div class="oh-value">From {{ $leaveRequest->from_date->format('d/m/Y') }} To {{ $leaveRequest->to_date->format('d/m/Y') }}</div>
                <div class="oh-label">Company</div>
                <div class="oh-value">My Company (San Francisco)</div>
                <div class="oh-label">Duration</div>
                <div class="oh-value">{{ $leaveRequest->duration_days }} Days ({{ $leaveRequest->duration_hours }} Hours)</div>
                <div class="oh-label">Description</div>
                <div class="oh-value">{{ $leaveRequest->description }}</div>
            </div>

            <div class="mt-4">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><button class="nav-link active">Pending Works</button></li>
                </ul>
                <table class="oh-list-table mt-3">
                    <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Description</th>
                        <th style="width: 40px"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($leaveRequest->pendingWorks as $work)
                        <tr>
                            <td>{{ $work->task }}</td>
                            <td>{{ $work->project }}</td>
                            <td>{{ $work->description }}</td>
                            <td><i class="bi bi-trash"></i></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-purple">Add a line</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="oh-chatter">
            <div class="d-flex gap-4 text-purple">
                <span>Send message</span>
                <span>Log note</span>
                <span><i class="bi bi-clock"></i> Schedule activity</span>
            </div>
            <div class="text-center my-3 fw-semibold">Planned activities</div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
