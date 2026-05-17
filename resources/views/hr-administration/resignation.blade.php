@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Employee Resignation'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Employee Resignation / Audrey Peterson</h1><a class="hr-primary">Edit</a> <a class="hr-secondary">Create</a></div>
            <div class="text-center pt-4"><a class="hr-secondary"><i class="bi bi-gear-fill"></i> Action</a></div>
            <div class="hr-pager"><span>1 / 1</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i></div>
        </div>
        <div class="d-flex justify-content-between px-2 pb-1"><div><a class="hr-primary">Confirm</a> <a class="hr-secondary">Cancel</a></div><div class="hr-stagebar m-0"><span class="hr-stage active">Draft</span><span class="hr-stage">Confirm</span></div></div>
        <div class="hr-pattern">
            <section class="hr-form-card">
                <h2 class="record-title">RES001</h2>
                <div class="hr-form-grid">
                    <div>
                        <div class="section-title">Employee Details</div>
                        <div class="hr-field-grid">
                            <label>Employee</label><span class="hr-link-value">Audrey Peterson</span>
                            <label>Department</label><span class="hr-link-value">Professional Services</span>
                            <label>Employee Contract</label><span>Contract For Audrey Peterson</span>
                        </div>
                    </div>
                    <div>
                        <div class="section-title">Dates</div>
                        <div class="hr-field-grid">
                            <label>Join Date</label><span>03/06/2019</span>
                            <label>Last Day of Employee</label><span>02/05/2022</span>
                            <label>Approved Last Day of Employee</label><span>02/15/2022</span>
                            <label>Notice Period</label><span>0</span>
                        </div>
                    </div>
                </div>
                <div class="section-title mt-4">Resignation Details</div>
                <div class="hr-field-grid" style="max-width: 420px;">
                    <label>Type</label><span>Normal Resignation</span>
                    <label>Reason</label><span>Personal reason</span>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('styles')
    <style>.record-title { font-size: 22px; font-weight: 700; margin-bottom: 28px; }.section-title { border-bottom: 1px solid #eff1f4; color: #6e36a2; font-size: 11px; font-weight: 700; margin-bottom: 12px; padding-bottom: 5px; }</style>
@endpush
