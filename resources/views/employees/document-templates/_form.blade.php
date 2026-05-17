@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">Code</label>
        <input name="code" class="form-control" value="{{ old('code', optional($template)->code ?? '') }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label fw-bold">Template Name</label>
        <input name="name" class="form-control" value="{{ old('name', optional($template)->name ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label fw-bold">Description</label>
        <input name="description" class="form-control" value="{{ old('description', optional($template)->description ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label fw-bold">Content</label>
        <textarea name="content" rows="10" class="form-control">{{ old('content', optional($template)->content ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="template-active" name="is_active" value="1" @checked(old('is_active', optional($template)->is_active ?? true))>
            <label class="form-check-label" for="template-active">Active Template</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn doc-primary">{{ $submitLabel }}</button>
    <a href="{{ route('employees.document-templates.index') }}" class="btn doc-secondary">Discard</a>
</div>

@once
    @push('styles')
        <style>
            .odoo-form-shell { background-color: #f4f4f4; background-image: radial-gradient(#d3d3d3 .6px, transparent .6px); background-size: 3px 3px; min-height: calc(100vh - 220px); padding: 14px 0 20px; }
            .odoo-form-card { background: #fff; border: 1px solid #c8ced8; margin: 0 auto; max-width: 1120px; padding: 20px; }
            .odoo-form-card .form-label { color: #111827; }
            .odoo-form-card .form-control, .odoo-form-card .form-select { border: 1px solid #cfd4dc; border-radius: 0; }
            .doc-primary { background: #7e57a3; border-color: #7e57a3; color: #fff; }
            .doc-primary:hover { background: #6f4b94; border-color: #6f4b94; color: #fff; }
            .doc-secondary { background: #fff; border: 1px solid #d0d5dd; color: #111827; }
            .doc-secondary:hover { background: #f9fafb; color: #111827; }
        </style>
    @endpush
@endonce
