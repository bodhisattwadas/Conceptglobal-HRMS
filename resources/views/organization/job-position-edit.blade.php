@extends('layouts.app', [
    'heading' => 'Organization',
    'subheading' => 'Edit Job Position',
])

@section('content')
    <nav class="odoo-topbar">
        <div class="odoo-top-left">
            <a href="{{ route('organization.index') }}" class="odoo-app-switcher" aria-label="Apps">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </a>
            <a href="{{ route('organization.index') }}" class="odoo-module-title">Organization</a>
            <a href="{{ route('organization.index', ['menu' => 'companies']) }}">Companies</a>
            <a href="{{ route('organization.index', ['menu' => 'departments']) }}">Departments</a>
            <a href="{{ route('organization.index', ['menu' => 'job-positions']) }}" class="active">Job Positions</a>
        </div>
    </nav>

    <div class="card table-card">
        <div class="card-header org-card-header fw-semibold">Edit Job Position</div>
        <div class="card-body">
            <form method="post" action="{{ route('organization.job-positions.update', $position) }}" class="vstack gap-3">
                @csrf
                @method('put')
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((int) old('department_id', $position->department_id) === $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Name</label>
                    <input name="name" value="{{ old('name', $position->name) }}" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Companies</label>
                    <select name="company_ids[]" class="form-select" multiple>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(collect(old('company_ids', $position->companies->pluck('id')->all()))->map(fn ($id) => (int) $id)->contains($company->id))>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-oh">Update Position</button>
                    <a href="{{ route('organization.index', ['menu' => 'job-positions']) }}" class="btn btn-oh-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .odoo-topbar {
            align-items: center;
            background: #7e57a3;
            color: #fff;
            display: flex;
            font-size: 13px;
            height: 44px;
            justify-content: space-between;
            margin: -1.5rem -1.5rem 14px;
            padding: 0 14px;
        }
        .odoo-topbar a { color: #fff; text-decoration: none; }
        .odoo-top-left { align-items: center; display: flex; gap: 24px; min-width: 0; }
        .odoo-topbar a.active { background: rgba(0,0,0,.1); margin: -13px 0; padding: 13px 14px; }
        .odoo-app-switcher { font-size: 16px; margin-right: -12px; }
        .odoo-module-title { font-size: 20px; line-height: 1; }
        .org-card-header {
            background: #f8f6fb;
            border-bottom: 1px solid #e4dcef;
            color: #6e4c94;
        }
        .table-card {
            border: 1px solid #d9dce3;
            border-radius: 0;
            max-width: 720px;
        }
        .btn-oh {
            background: #7e57a3;
            border-color: #7e57a3;
            color: #fff;
        }
        .btn-oh:hover {
            background: #6f4b94;
            border-color: #6f4b94;
            color: #fff;
        }
        .btn-oh-light {
            background: #fff;
            border: 1px solid #cfd6df;
            color: #111827;
        }
        .btn-oh-light:hover {
            background: #f3f4f6;
            border-color: #c4cbd5;
            color: #111827;
        }
    </style>
@endpush
