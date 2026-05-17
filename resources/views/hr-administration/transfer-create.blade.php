@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Transfer'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Transfer / New</h1><a class="hr-primary">Save</a> <a class="hr-secondary">Discard</a></div>
            <div class="text-center pt-4"><a class="hr-primary">Transfer</a></div>
            <div class="hr-stagebar"><span class="hr-stage active">New</span><span class="hr-stage">Transferred</span><span class="hr-stage">Done</span></div>
        </div>
        <div class="hr-pattern">
            <section class="hr-form-card" style="max-width: 940px;">
                <h2 class="form-heading">New</h2>
                <div class="hr-form-grid">
                    <div class="hr-field-grid">
                        <label>Employee</label><select><option>Anita Oliver</option></select>
                        <label>Date</label><input value="Anita Oliver">
                        <label>Transfer To</label><select><option>New Company</option></select>
                    </div>
                    <div class="hr-field-grid">
                        <label>Company</label><span class="hr-link-value">YourCompany</span>
                        <label>Responsible</label><span class="hr-link-value">Mitchell Admin</span>
                    </div>
                </div>
                <div class="hr-tab">Internal Notes</div>
                <textarea class="hr-textarea" style="height: 42px;"></textarea>
            </section>
        </div>
    </div>
@endsection

@push('styles')
    <style>.form-heading { font-size: 15px; font-weight: 700; margin-bottom: 22px; }</style>
@endpush
