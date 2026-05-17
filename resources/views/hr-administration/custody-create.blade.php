@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Custody'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Custody / New</h1><a class="hr-primary">Save</a> <a class="hr-secondary">Discard</a></div>
            <div class="text-center pt-4"><a class="hr-primary">Send For Approval</a></div>
            <div class="hr-stagebar"><span class="hr-stage active">Draft</span><span class="hr-stage">Waiting For Approval</span><span class="hr-stage">Approved</span><span class="hr-stage">Returned</span></div>
        </div>
        <div class="hr-pattern">
            <section class="hr-form-card">
                <div class="hr-form-grid">
                    <div class="hr-field-grid">
                        <label>Employee</label><select><option>Anita Oliver</option></select>
                        <label>Property</label><select><option>Laptop</option></select>
                        <label>Reason</label><input value="Work from Home">
                    </div>
                    <div class="hr-field-grid">
                        <label>Requested Date</label><input value="02/15/2022">
                        <label>Return Date</label><input value="02/01/2023">
                        <label>Company</label><span class="hr-link-value">YourCompany</span>
                    </div>
                </div>
                <div class="hr-tab">Notes</div>
                <textarea class="hr-textarea"></textarea>
            </section>
        </div>
    </div>
@endsection
