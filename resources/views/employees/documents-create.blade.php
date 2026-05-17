@extends('layouts.app', [
    'heading' => 'Employees',
    'subheading' => $employee->full_name.' / Documents / New',
])

@section('content')
    @include('employees._module_nav')

    <div class="odoo-form-title">Employees / {{ $employee->full_name }} / Documents / New</div>
    <form method="post" action="{{ route('employees.documents.store', $employee) }}">
        @csrf
        <div class="odoo-doc-actions">
            <button class="odoo-primary">Save</button>
            <a href="{{ route('employees.show', $employee) }}" class="odoo-secondary">Discard</a>
        </div>
        <div class="odoo-pattern-page">
            <section class="odoo-doc-sheet">
                <div class="odoo-doc-grid">
                    <div>
                        <label>Document Number</label>
                        <input name="document_number" autofocus>
                        <label>Employee</label>
                        <div class="input-with-icon">
                            <select name="employee_id"><option>{{ $employee->full_name }}</option></select>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </div>
                        <label>Document Type</label>
                        <select name="document_type">
                            <option></option>
                            @foreach(($documentTypes ?? []) as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <label>Attachment</label>
                        <button class="odoo-secondary attachment" type="button"><i class="bi bi-paperclip"></i> Attachment</button>
                    </div>
                    <div>
                        <label>Issue Date</label>
                        <input name="issue_date" value="02/14/2022">
                        <label>Expiry Date</label>
                        <input name="expiry_date">
                        <label>Notification Type</label>
                        <select name="notification_type"><option></option><option>Before Expiry</option><option>After Issue</option></select>
                        <label>Days</label>
                        <input name="days" value="0">
                    </div>
                </div>
                <div class="odoo-doc-tab">Description</div>
                <textarea name="description"></textarea>
            </section>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        body { background: #fff; font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
        .odoo-topbar { margin: -1.5rem -1.5rem 0; }
        .content > section { padding-bottom: 0 !important; }
        .odoo-form-title { color: #6e4c94; font-size: 18px; padding: 11px 15px 10px; }
        .odoo-doc-actions { background: #fff; border-bottom: 1px solid #d9dde4; padding: 0 15px 9px; }
        .odoo-primary, .odoo-secondary {
            background: #7e57a3;
            border: 1px solid #7e57a3;
            color: #fff;
            display: inline-block;
            font-size: 13px;
            padding: 7px 12px;
            text-decoration: none;
        }
        .odoo-secondary { background: #fff; border-color: #d8dde6; color: #111827; }
        .odoo-pattern-page {
            background-color: #f4f4f4;
            background-image: radial-gradient(#d3d3d3 .6px, transparent .6px);
            background-size: 3px 3px;
            min-height: calc(100vh - 83px);
            padding: 13px 0;
        }
        .odoo-doc-sheet {
            background: #fff;
            border: 1px solid #c8ced8;
            box-shadow: 0 1px 2px rgba(0,0,0,.08);
            margin: 0 auto;
            max-width: 1140px;
            min-height: 340px;
            padding: 34px 16px 48px;
        }
        .odoo-doc-grid { display: grid; gap: 36px; grid-template-columns: 1fr 1fr; }
        .odoo-doc-grid > div { display: grid; grid-template-columns: 150px 1fr; row-gap: 8px; }
        label { color: #111827; font-weight: 700; padding-top: 5px; }
        input, select, textarea {
            border: 1px solid #ccd1d8;
            border-radius: 3px;
            font-size: 13px;
            height: 26px;
            padding: 3px 5px;
            width: 100%;
        }
        input:focus { background: #d8d3ff; border-color: #7e57a3; outline: none; }
        .input-with-icon { display: flex; gap: 4px; }
        .input-with-icon i { color: #6e4c94; font-size: 20px; }
        .attachment { justify-self: start; padding: 5px 10px; }
        .odoo-doc-tab {
            border: 1px solid #d8dde6;
            border-bottom: 0;
            color: #6e4c94;
            display: inline-block;
            margin-top: 26px;
            padding: 8px 13px;
        }
        textarea { display: block; height: 51px; resize: none; }
        @media (max-width: 900px) {
            .odoo-doc-grid, .odoo-doc-grid > div { grid-template-columns: 1fr; }
        }
    </style>
@endpush
