@extends('layouts.app', [
    'heading' => 'Organization',
    'subheading' => 'Companies, departments, positions, and roles',
])

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card table-card">
                <div class="card-header bg-white fw-semibold">New Company</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.companies.store') }}" class="vstack gap-3">
                        @csrf
                        <input name="name" class="form-control" placeholder="Company name" required>
                        <input name="city" class="form-control" placeholder="City">
                        <input name="country" class="form-control" placeholder="Country">
                        <textarea name="address" class="form-control" rows="3" placeholder="Address"></textarea>
                        <button class="btn btn-danger">Create Company</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card table-card">
                <div class="card-header bg-white fw-semibold">Companies</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
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
                <div class="card-header bg-white fw-semibold">New Department</div>
                <div class="card-body">
                    <form method="post" action="{{ route('organization.departments.store') }}" class="vstack gap-3">
                        @csrf
                        <input name="name" class="form-control" placeholder="Department name" required>
                        <select name="company_ids[]" class="form-select" multiple>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-danger">Create Department</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card table-card">
                <div class="card-header bg-white fw-semibold">Departments</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
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

        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header bg-white fw-semibold">New Job Position</div>
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
                        <button class="btn btn-danger">Create Position</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card table-card">
                <div class="card-header bg-white fw-semibold">New Job Role</div>
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
                        <button class="btn btn-danger">Create Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
