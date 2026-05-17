@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Announcements'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Announcements / New</h1><a class="hr-primary">Save</a> <a class="hr-secondary">Discard</a></div>
            <div class="text-center pt-4"><a class="hr-primary">Send For Approval</a></div>
            <div class="hr-stagebar"><span class="hr-stage active">Draft</span><span class="hr-stage">Waiting For Approval</span><span class="hr-stage">Approved</span></div>
        </div>
        <div class="hr-pattern">
            <section class="hr-form-card">
                <div class="hr-field-grid" style="max-width: 820px;">
                    <label>Code No:</label><span></span>
                </div>
                <div class="hr-form-grid mt-4">
                    <div class="hr-field-grid">
                        <label>General Announcement <i class="bi bi-info-circle-fill"></i></label><span></span>
                        <label>Title</label><input value="Farewell to HR Manager">
                        <label>Start Date</label><input value="02/16/2022">
                        <label>Attachment</label><button class="hr-secondary" type="button"><i class="bi bi-paperclip"></i> Attachment</button>
                    </div>
                    <div class="hr-field-grid">
                        <label></label><span></span>
                        <label></label><span></span>
                        <label>End Date</label><input value="02/16/2022">
                        <label>Requested Date</label><span class="hr-link-value">02/15/2022</span>
                        <label>Company</label><span class="hr-link-value">YourCompany</span>
                    </div>
                </div>
                <div class="hr-tab">Letter</div>
                <textarea class="hr-textarea"></textarea>
            </section>
        </div>
    </div>
@endsection
