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
    <button class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('employees.document-templates.index') }}" class="btn btn-outline-secondary">Discard</a>
</div>
