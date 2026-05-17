@extends('layouts.app', [
    'heading' => 'Dashboard',
    'subheading' => 'The first Laravel build slice for Horilla HRMS',
])

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card metric">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small">Companies</div>
                        <div class="display-6 fw-semibold">{{ $companyCount }}</div>
                    </div>
                    <i class="bi bi-building fs-1 text-danger"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small">Departments</div>
                        <div class="display-6 fw-semibold">{{ $departmentCount }}</div>
                    </div>
                    <i class="bi bi-diagram-3 fs-1 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small">Employees</div>
                        <div class="display-6 fw-semibold">{{ $employeeCount }}</div>
                    </div>
                    <i class="bi bi-people fs-1 text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small">Job Positions</div>
                        <div class="display-6 fw-semibold">{{ $jobPositionCount }}</div>
                    </div>
                    <i class="bi bi-person-workspace fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card table-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Recent Employees</div>
                        <div class="small text-secondary">Early employee module preview</div>
                    </div>
                    <a href="{{ route('employees.create') }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-plus-lg"></i>
                        Add Employee
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Department</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($recentEmployees as $employee)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $employee->full_name }}</div>
                                    <div class="small text-secondary">{{ $employee->email }}</div>
                                </td>
                                <td>{{ $employee->workInformation?->company?->name ?? '-' }}</td>
                                <td>{{ $employee->workInformation?->department?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $employee->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    No employees yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card table-card">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Build Slice</div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            Laravel app scaffold
                        </div>
                        <div class="list-group-item px-0 d-flex gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            MySQL-ready configuration
                        </div>
                        <div class="list-group-item px-0 d-flex gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            Core HR data model
                        </div>
                        <div class="list-group-item px-0 d-flex gap-2">
                            <i class="bi bi-circle text-secondary"></i>
                            Auth and permissions
                        </div>
                        <div class="list-group-item px-0 d-flex gap-2">
                            <i class="bi bi-circle text-secondary"></i>
                            Company scoping middleware
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
