@extends('layouts.app', [
    'heading' => 'Organization',
    'subheading' => 'Companies, departments, positions, and roles',
])

@section('content')
    <nav class="odoo-topbar">
        <div class="odoo-top-left">
            <a href="{{ route('organization.index') }}" class="odoo-app-switcher" aria-label="Apps">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </a>
            <a href="{{ route('organization.index') }}" class="odoo-module-title">Organization</a>
            <a href="{{ route('organization.index', ['menu' => 'companies']) }}" @class(['active' => $menu === 'companies'])>Companies</a>
            <a href="{{ route('organization.index', ['menu' => 'departments']) }}" @class(['active' => $menu === 'departments'])>Departments</a>
            <a href="{{ route('organization.index', ['menu' => 'job-positions']) }}" @class(['active' => $menu === 'job-positions'])>Job Positions</a>
            <a href="{{ route('organization.index', ['menu' => 'job-roles']) }}" @class(['active' => $menu === 'job-roles'])>Job Roles</a>
        </div>
    </nav>
    <div class="row g-4">
        @if($menu === 'companies')
        <div class="col-xl-4">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">New Company</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.companies.store') }}" class="vstack gap-3">
                        @csrf
                        <input name="name" class="form-control" placeholder="Company name" required>
                        <input name="city" class="form-control" placeholder="City">
                        <input name="country" class="form-control" placeholder="Country">
                        <textarea name="address" class="form-control" rows="3" placeholder="Address"></textarea>
                        <button class="btn btn-oh">Create Company</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">Companies</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="org-table-head">
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td class="fw-semibold">{{ $company->name }}</td>
                                <td>{{ collect([$company->city, $company->country])->filter()->join(', ') ?: '-' }}</td>
                                <td><span class="badge text-bg-success">Active</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary py-4">No companies yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">New Department</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.departments.store') }}" class="vstack gap-3">
                        @csrf
                        <input name="name" class="form-control" placeholder="Department name" required>
                        <select name="company_ids[]" class="form-select" multiple>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-oh">Create Department</button>
                    </form>
                </div>
            </div>
        </div>
        @elseif($menu === 'departments')
        <div class="col-xl-4">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">New Department</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.departments.store') }}" class="vstack gap-3">
                        @csrf
                        <input name="name" class="form-control" placeholder="Department name" required>
                        <select name="company_ids[]" class="form-select" multiple>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-oh">Create Department</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">Departments</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="org-table-head">
                        <tr>
                            <th>Name</th>
                            <th>Companies</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($departments as $department)
                            <tr>
                                <td class="fw-semibold">{{ $department->name }}</td>
                                <td>{{ $department->companies->pluck('name')->join(', ') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary py-4">No departments yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @elseif($menu === 'job-positions')
        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">New Job Position</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.job-positions.store') }}" class="vstack gap-3">
                        @csrf
                        <select name="department_id" class="form-select" required>
                            <option value="">Select department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <input name="name" class="form-control" placeholder="Job position" required>
                        <select name="company_ids[]" class="form-select" multiple>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-oh">Create Position</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">Job Positions</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="org-table-head">
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Companies</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($jobPositions as $position)
                            <tr>
                                <td class="fw-semibold">{{ $position->name }}</td>
                                <td>{{ $position->department?->name ?: '-' }}</td>
                                <td>{{ $position->companies->pluck('name')->join(', ') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary py-4">No job positions yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @elseif($menu === 'job-roles')
        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">New Job Role</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.job-roles.store') }}" class="vstack gap-3">
                        @csrf
                        <select name="job_position_id" class="form-select" required>
                            <option value="">Select position</option>
                            @foreach ($jobPositions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                        <input name="name" class="form-control" placeholder="Job role" required>
                        <select name="company_ids[]" class="form-select" multiple>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-oh">Create Role</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header org-card-header fw-semibold">Job Roles</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="org-table-head">
                        <tr>
                            <th>Name</th>
                            <th>Job Position</th>
                            <th>Companies</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($jobRoles as $role)
                            <tr>
                                <td class="fw-semibold">{{ $role->name }}</td>
                                <td>{{ $role->jobPosition?->name ?: '-' }}</td>
                                <td>{{ $role->companies->pluck('name')->join(', ') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary py-4">No job roles yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
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
        .odoo-top-left, .odoo-top-right { align-items: center; display: flex; gap: 24px; min-width: 0; }
        .odoo-top-right { gap: 14px; }
        .odoo-app-switcher { font-size: 16px; margin-right: -12px; }
        .odoo-module-title {
            font-size: 20px;
            line-height: 1;
        }
        .odoo-icon-badge { position: relative; }
        .odoo-icon-badge b {
            background: #00a09d;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            left: 9px;
            line-height: 1;
            padding: 2px 5px;
            position: absolute;
            top: -10px;
        }
        .odoo-user-pic {
            align-items: center;
            background: #b78b6a;
            border-radius: 50%;
            display: inline-flex;
            font-size: 10px;
            height: 24px;
            justify-content: center;
            width: 24px;
        }
        .org-card-header {
            background: #f8f6fb;
            border-bottom: 1px solid #e4dcef;
            color: #6e4c94;
        }
        .org-table-head th {
            background: #efedf3 !important;
            color: #433259;
            font-weight: 700;
        }
        .table-card {
            border: 1px solid #d9dce3;
            border-radius: 0;
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
    </style>
@endpush
