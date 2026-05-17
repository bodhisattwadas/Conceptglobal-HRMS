@extends('layouts.app', ['heading' => 'Attendances', 'subheading' => 'Regularization Request'])

@section('content')
    @include('attendance._nav')

    <div class="oh-page-title">
        <h1>Regularization Request / {{ $regularization->employee->full_name }}</h1>
        <div class="small text-secondary">1 / 1 &nbsp; <i class="bi bi-chevron-left"></i> &nbsp; <i class="bi bi-chevron-right"></i></div>
    </div>
    <div class="oh-actions">
        <div class="d-flex gap-2">
            <button class="btn btn-oh">Edit</button>
            <button class="btn btn-oh-light">Create</button>
        </div>
        <button class="btn btn-oh-light"><i class="bi bi-gear-fill"></i> Action</button>
        <div></div>
    </div>
    <div class="px-3 py-1 bg-white border-bottom d-flex justify-content-between">
        <div class="d-flex gap-1">
            <form method="post" action="{{ route('attendance.regularization.approve', $regularization) }}">
                @csrf
                @method('patch')
                <button class="btn btn-oh btn-sm">Approve</button>
            </form>
            <form method="post" action="{{ route('attendance.regularization.reject', $regularization) }}">
                @csrf
                @method('patch')
                <button class="btn btn-oh btn-sm">Reject</button>
            </form>
        </div>
        <div class="oh-statusbar">
            @foreach (['draft' => 'Draft', 'requested' => 'Requested', 'approved' => 'Approved'] as $state => $label)
                <span @class(['oh-state', 'active' => $regularization->status === $state])>{{ $label }}</span>
            @endforeach
        </div>
    </div>
    <div class="oh-pattern">
        <div class="oh-sheet">
            <div class="oh-row">
                <div class="oh-label">Regularization Category</div>
                <div class="oh-value">{{ $regularization->category }}</div>
                <div class="oh-label">From Date</div>
                <div class="oh-value">{{ $regularization->from_at->format('d/m/Y H:i:s') }}</div>
                <div class="oh-label">Reason</div>
                <div class="oh-value">{{ $regularization->reason }}</div>
                <div class="oh-label">To Date</div>
                <div class="oh-value">{{ $regularization->to_at->format('d/m/Y H:i:s') }}</div>
                <div class="oh-label">Employee</div>
                <div class="oh-value">{{ $regularization->employee->full_name }}</div>
            </div>
        </div>
        <div class="oh-chatter">
            <div class="d-flex gap-4 text-purple">
                <span>Send message</span>
                <span>Log note</span>
                <span><i class="bi bi-clock"></i> Schedule activity</span>
            </div>
            <div class="text-center my-3 fw-semibold">Today</div>
            <div class="oh-message">
                <strong>Mitchell Admin</strong> <span class="text-secondary">- now</span>
                <div class="ms-4 mt-1">State: Draft → {{ ucfirst($regularization->status) }}</div>
            </div>
            <div class="oh-message">
                <strong>OdooBot</strong> <span class="text-secondary">- 4 hours ago</span>
                <div>Approval Request created</div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .oh-sheet { max-width: 1140px; min-height: 330px; padding-top: 38px; }
        .oh-chatter { max-width: 1140px; }
        .oh-message { border: 0; }
        .text-purple { color: #6e36a2; }
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
