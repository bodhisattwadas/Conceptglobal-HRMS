@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Legal Management'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Legal Management / LC0001</h1><a class="hr-primary">Edit</a> <a class="hr-secondary">Create</a></div>
            <div class="text-center pt-4"><a class="hr-secondary"><i class="bi bi-gear-fill"></i> Action</a></div>
            <div class="hr-pager"><span>1 / 1</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i></div>
        </div>
        <div class="d-flex justify-content-between px-2 pb-1"><div><a class="hr-secondary">Process</a> <a class="hr-secondary">Cancel</a></div><div class="hr-stagebar m-0"><span class="hr-stage active">Draft</span><span class="hr-stage">Running</span><span class="hr-stage">Won</span></div></div>
        <div class="hr-pattern">
            <section class="hr-form-card">
                <h2 class="record-title">LC0001</h2>
                <div class="hr-form-grid">
                    <div class="hr-field-grid">
                        <label>Reference Number</label><span class="hr-link-value">RC01</span>
                        <label>Party 1</label><span class="hr-link-value">MyCompany</span>
                        <label>Party 2</label><span class="hr-link-value">Employee</span>
                        <label>Employee</label><span class="hr-link-value">Roshan Andrews</span>
                        <label>Court Name</label><span>court1</span>
                        <label>Judge</label><span>Judge1</span>
                        <label>Lawyer</label><span class="hr-link-value">Abc company</span>
                        <label>Company</label><span class="hr-link-value">YourCompany</span>
                    </div>
                    <div class="hr-field-grid">
                        <label>Date</label><span>03/02/2020</span>
                        <label>Hearing Date</label><span>05/02/2020</span>
                    </div>
                </div>
                <div class="hr-tab">Case Details</div>
                <textarea class="hr-textarea"></textarea>
            </section>
        </div>
    </div>
@endsection

@push('styles')
    <style>.record-title { font-size: 22px; font-weight: 700; margin-bottom: 26px; }</style>
@endpush
