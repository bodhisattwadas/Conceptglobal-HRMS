@extends('layouts.openhrms', ['title' => 'Attendances'])
@include('attendance._nav')

@section('content')
    <div class="oh-page-title">
        <h1>Attendances / {{ $device->machine_ip }}</h1>
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
    <div class="px-3 py-1 bg-white border-bottom">
        <button class="btn btn-oh btn-sm"><i class="bi bi-x-lg"></i> Clear Data</button>
        <button class="btn btn-oh btn-sm"><i class="bi bi-download"></i> Download Data</button>
    </div>
    <div class="oh-pattern">
        <div class="oh-sheet">
            <div class="mb-5">
                <div class="oh-label">Machine IP</div>
                <div class="display-6 fw-bold">{{ $device->machine_ip }}</div>
            </div>
            <div class="oh-row">
                <div class="oh-label">Port No</div>
                <div class="oh-value">{{ $device->port }}</div>
                <div class="oh-label">Company</div>
                <div class="oh-value">{{ $device->company?->name ?? '-' }}</div>
                <div class="oh-label">Working Address</div>
                <div class="oh-value">{{ $device->working_address ?? '-' }}</div>
            </div>
        </div>
    </div>
@endsection
