@extends('layouts.app', [
    'heading' => 'Employees',
    'subheading' => 'Kanban employee directory with HR filters',
])

@section('content')
    @include('employees._module_nav')

    <div class="employee-directory">
        <aside class="employee-facets">
            <div class="facet-title">Company</div>
            <a href="{{ route('employees.index', request()->except('company_id', 'page')) }}" @class(['active' => !request('company_id')])>
                <span>All</span>
                <span>{{ $employeeTotal }}</span>
            </a>
            @foreach ($companies as $company)
                <a href="{{ route('employees.index', [...request()->except('page'), 'company_id' => $company->id]) }}" @class(['active' => (int) request('company_id') === $company->id])>
                    <span>{{ $company->name }}</span>
                    <span>{{ $companyCounts[$company->id] ?? 0 }}</span>
                </a>
            @endforeach

            <div class="facet-title mt-4">Department</div>
            <a href="{{ route('employees.index', request()->except('department_id', 'page')) }}" @class(['active' => !request('department_id')])>
                <span>All</span>
                <span>{{ $employeeTotal }}</span>
            </a>
            @foreach ($departments as $department)
                <a href="{{ route('employees.index', [...request()->except('page'), 'department_id' => $department->id]) }}" @class(['active' => (int) request('department_id') === $department->id])>
                    <span>{{ $department->name }}</span>
                    <span>{{ $departmentCounts[$department->id] ?? 0 }}</span>
                </a>
            @endforeach
        </aside>

        <section class="employee-board">
            <div class="employee-toolbar">
                <div class="d-flex gap-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">Create</a>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary">Discard Filters</a>
                </div>
                <form method="get" class="employee-search">
                    @foreach (request()->except('search', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search...">
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                </form>
                <div class="d-flex align-items-center gap-2 small text-secondary">
                    <span>{{ $employees->firstItem() ?? 0 }}-{{ $employees->lastItem() ?? 0 }} / {{ $employees->total() }}</span>
                    <a class="btn btn-sm btn-light border" href="{{ route('employees.index', [...request()->except('page'), 'view' => 'kanban']) }}"><i class="bi bi-grid-3x3-gap-fill"></i></a>
                    <a class="btn btn-sm btn-light border" href="{{ route('employees.index', [...request()->except('page'), 'view' => 'list']) }}"><i class="bi bi-list-ul"></i></a>
                </div>
            </div>

            @if ($viewMode === 'list')
                <div class="table-responsive bg-white border">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Work Email</th>
                            <th>Department</th>
                            <th>Job Position</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td><a href="{{ route('employees.show', $employee) }}" class="fw-semibold text-decoration-none">{{ $employee->full_name }}</a></td>
                                <td>{{ $employee->workInformation?->email ?? $employee->email }}</td>
                                <td>{{ $employee->workInformation?->department?->name ?? '-' }}</td>
                                <td>{{ $employee->workInformation?->jobPosition?->name ?? '-' }}</td>
                                <td>{{ $employee->workInformation?->reportingManager?->full_name ?? '-' }}</td>
                                <td>{{ $employee->is_active ? 'Active' : 'Archived' }}</td>
                                <td class="text-end"><a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="employee-kanban">
                    @forelse ($employees as $employee)
                        <article class="employee-card">
                            <a href="{{ route('employees.show', $employee) }}" class="employee-avatar" style="background: {{ $employee->card_color }}">
                                @if ($employee->profile_photo_url)
                                    <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                                @else
                                    <span>{{ $employee->initials }}</span>
                                @endif
                            </a>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <a href="{{ route('employees.show', $employee) }}" class="employee-name">{{ $employee->full_name }}</a>
                                    <span class="status-dot {{ $employee->is_active ? 'online' : '' }}"></span>
                                </div>
                                <div class="employee-title">{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</div>
                                <div class="employee-meta">
                                    <i class="bi bi-building"></i>
                                    {{ $employee->workInformation?->department?->name ?? 'No department' }}
                                </div>
                                <div class="employee-meta">{{ $employee->workInformation?->email ?? $employee->email }}</div>
                                <div class="employee-meta">{{ $employee->workInformation?->work_mobile ?? $employee->phone ?? '-' }}</div>
                            </div>
                            <div class="employee-card-actions">
                                <a href="{{ route('employees.edit', $employee) }}"><i class="bi bi-pencil"></i></a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">No employees match these filters.</div>
                    @endforelse
                </div>
            @endif

            @if ($employees->hasPages())
                <div class="mt-3">{{ $employees->links() }}</div>
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .employee-module-nav {
            background: #6f5b9a;
            color: #fff;
            display: flex;
            justify-content: space-between;
            margin: -1.5rem -1.5rem 1rem;
            padding: .55rem .85rem;
        }
        .employee-module-nav a {
            color: rgba(255,255,255,.82);
            font-size: .82rem;
            text-decoration: none;
        }
        .employee-module-nav a.active,
        .employee-module-nav a:hover {
            color: #fff;
        }
        .employee-directory {
            background: #fff;
            border: 1px solid #d9dee7;
            display: grid;
            grid-template-columns: 220px 1fr;
            min-height: 650px;
        }
        .employee-facets {
            border-right: 1px solid #e2e6ee;
            padding: .85rem;
        }
        .facet-title {
            color: #51446f;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .employee-facets a {
            align-items: center;
            color: #26313f;
            display: flex;
            font-size: .82rem;
            justify-content: space-between;
            padding: .35rem .5rem;
            text-decoration: none;
        }
        .employee-facets a.active {
            background: #d8eff8;
        }
        .employee-board {
            min-width: 0;
            padding: .75rem;
        }
        .employee-toolbar {
            align-items: center;
            display: grid;
            gap: .75rem;
            grid-template-columns: auto 1fr auto;
            margin-bottom: .75rem;
        }
        .employee-search {
            display: flex;
            justify-self: end;
            max-width: 540px;
            width: 100%;
        }
        .employee-kanban {
            display: grid;
            gap: .55rem;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
        }
        .employee-card {
            border: 1px solid #dbe1ea;
            display: flex;
            gap: .65rem;
            min-height: 105px;
            padding: .55rem;
            position: relative;
        }
        .employee-avatar {
            align-items: center;
            color: #fff;
            display: flex;
            flex: 0 0 78px;
            font-size: 2rem;
            font-weight: 600;
            justify-content: center;
            overflow: hidden;
            text-decoration: none;
        }
        .employee-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .employee-name {
            color: #1f2937;
            display: block;
            font-size: .88rem;
            font-weight: 700;
            overflow: hidden;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .employee-title,
        .employee-meta {
            color: #4b5563;
            font-size: .76rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status-dot {
            background: #f59e0b;
            border-radius: 50%;
            flex: 0 0 9px;
            height: 9px;
            margin-top: .25rem;
            width: 9px;
        }
        .status-dot.online {
            background: #22c55e;
        }
        .employee-card-actions {
            bottom: .4rem;
            position: absolute;
            right: .5rem;
        }
        .employee-card-actions a {
            color: #7b8190;
        }
        .empty-state {
            border: 1px dashed #cbd5e1;
            color: #64748b;
            grid-column: 1 / -1;
            padding: 3rem;
            text-align: center;
        }
        @media (max-width: 1200px) {
            .employee-kanban { grid-template-columns: repeat(2, minmax(220px, 1fr)); }
        }
        @media (max-width: 900px) {
            .employee-directory { grid-template-columns: 1fr; }
            .employee-facets { border-right: 0; border-bottom: 1px solid #e2e6ee; }
            .employee-toolbar { grid-template-columns: 1fr; }
            .employee-search { justify-self: stretch; max-width: none; }
            .employee-kanban { grid-template-columns: 1fr; }
        }
    </style>
@endpush
