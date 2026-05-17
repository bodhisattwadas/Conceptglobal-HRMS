@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Shifts'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page shift-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Shift Working Time</h1><button class="hr-primary" data-bs-toggle="modal" data-bs-target="#shiftModal">Create</button></div>
            <div></div>
            <div class="hr-pager"><span>1-4 / 4</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i><span class="hr-view"><i class="bi bi-grid-3x3-gap-fill"></i></span><i class="bi bi-list-ul"></i></div>
        </div>
        <div class="shift-card-grid">
            <article class="shift-card"><h2>Standard 40 hours/week</h2><span>(Management)</span></article>
        </div>
    </div>

    <div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shift-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-house-fill"></i> Employee Shift</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="hr-form-grid">
                        <div class="hr-field-grid">
                            <label>Department</label><select><option>Professional Services</option>@foreach ($departments as $department)<option>{{ $department->name }}</option>@endforeach</select>
                            <label>Start Date</label><input>
                        </div>
                        <div class="hr-field-grid">
                            <label>End Date</label><input>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button class="hr-primary">Generate</button>
                    <button class="hr-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .shift-page { min-height: calc(100vh - 122px); }
        .shift-card-grid { padding: 0 10px; }
        .shift-card { background: #fff; border: 1px solid #d8dde6; min-height: 72px; padding: 18px; width: 250px; }
        .shift-card h2 { font-size: 13px; font-weight: 700; margin: 0 0 20px; }
        .shift-card span { color: #6e36a2; font-size: 12px; float: right; }
        .shift-modal { border-radius: 0; }
        .shift-modal .modal-header { border-bottom: 0; padding-bottom: 0; }
        .shift-modal .modal-title { color: #34234d; font-size: 13px; font-weight: 700; }
        .modal-backdrop.show { opacity: .55; }
    </style>
@endpush
