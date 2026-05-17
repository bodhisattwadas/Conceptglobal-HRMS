@extends('layouts.app', [
    'heading' => 'Employees',
    'subheading' => 'Employee database',
])

@section('content')
    @include('employees._module_nav')

    <div class="odoo-list-page">
        <div class="odoo-list-header">
            <div>
                <h1>Employees</h1>
                <a href="{{ route('employees.create') }}" class="odoo-primary">Create</a>
            </div>
            <div class="odoo-search-panel">
                <form method="get" class="odoo-search">
                    @foreach (request()->except('search', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input name="search" value="{{ request('search') }}" placeholder="Search...">
                    <button aria-label="Search"><i class="bi bi-search"></i></button>
                </form>
                <div class="odoo-toolbar-line">
                    <span><i class="bi bi-funnel-fill"></i> Filters</span>
                    <span><i class="bi bi-list"></i> Group By</span>
                    <span><i class="bi bi-star-fill"></i> Favorites</span>
                </div>
            </div>
            <div class="odoo-pager">
                <span>{{ $employees->firstItem() ?? 0 }}-{{ $employees->lastItem() ?? 0 }} / {{ $employees->total() }}</span>
                <a href="#"><i class="bi bi-chevron-left"></i></a>
                <a href="#"><i class="bi bi-chevron-right"></i></a>
                <a @class(['active' => $viewMode === 'kanban']) href="{{ route('employees.index', [...request()->except('page'), 'view' => 'kanban']) }}"><i class="bi bi-grid-3x3-gap-fill"></i></a>
                <a @class(['active' => $viewMode === 'list']) href="{{ route('employees.index', [...request()->except('page'), 'view' => 'list']) }}"><i class="bi bi-list-ul"></i></a>
                <a @class(['active' => $viewMode === 'activity']) href="{{ route('employees.index', [...request()->except('page'), 'view' => 'activity']) }}"><i class="bi bi-clock"></i></a>
            </div>
        </div>

        <div class="odoo-directory">
            <aside class="odoo-departments">
                <div class="odoo-sidebar-title"><i class="bi bi-people-fill"></i> DEPARTMENT</div>
                <a href="{{ route('employees.index', request()->except('department_id', 'page')) }}" @class(['active' => !request('department_id')])>
                    <span>All</span>
                    <span></span>
                </a>
                @foreach ($departments as $department)
                    <a href="{{ route('employees.index', [...request()->except('page'), 'department_id' => $department->id]) }}" @class(['active' => (int) request('department_id') === $department->id])>
                        <span>{{ $department->name }}</span>
                        <span>{{ $departmentCounts[$department->id] ?? 0 }}</span>
                    </a>
                @endforeach
            </aside>

            <main class="odoo-employees">
                @if ($viewMode === 'list')
                    <table class="odoo-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Name</th>
                                <th>Work Email</th>
                                <th>Department</th>
                                <th>Job Position</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a></td>
                                    <td>{{ $employee->workInformation?->email ?? $employee->email }}</td>
                                    <td>{{ $employee->workInformation?->department?->name }}</td>
                                    <td>{{ $employee->workInformation?->jobPosition?->name }}</td>
                                    <td>{{ $employee->workInformation?->work_mobile ?? $employee->phone }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif ($viewMode === 'activity')
                    <table class="odoo-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Next Activity</th>
                                <th>Status</th>
                                <th>Responsible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a></td>
                                    <td><i class="bi bi-clock-history text-secondary"></i> No activity scheduled</td>
                                    <td>{{ $employee->is_active ? 'Active' : 'Archived' }}</td>
                                    <td>Mitchell Admin</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="odoo-card-grid">
                        @forelse ($employees as $employee)
                            <article class="odoo-employee-card">
                                <a href="{{ route('employees.show', $employee) }}" class="odoo-card-photo">
                                    @if ($employee->profile_photo_url)
                                        <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                                    @else
                                        <span>{{ $employee->initials }}</span>
                                    @endif
                                </a>
                                <div class="odoo-card-copy">
                                    <div class="odoo-card-head">
                                        <a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a>
                                        <i @class(['odoo-dot', 'online' => $employee->full_name === 'Mitchell Admin'])></i>
                                    </div>
                                    <div class="odoo-job">{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</div>
                                    <div class="odoo-tags">
                                        @if ($employee->workInformation?->employment_type)
                                            <span class="red-dot"></span>{{ $employee->workInformation->employment_type }}
                                        @endif
                                        @if ($employee->workInformation?->jobPosition?->name === 'Consultant')
                                            <span class="blue-dot"></span>Consultant
                                        @endif
                                    </div>
                                    <div>{{ $employee->workInformation?->email ?? $employee->email }}</div>
                                    <div>{{ $employee->workInformation?->work_mobile ?? $employee->phone }}</div>
                                </div>
                                <i class="bi bi-clock odoo-card-clock"></i>
                                @if (in_array($employee->full_name, ['Mitchell Admin', 'Marc Demo'], true))
                                    <i class="bi bi-chat-fill odoo-card-chat"></i>
                                @endif
                            </article>
                        @empty
                            <div class="odoo-empty">No employees match these filters.</div>
                        @endforelse
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body { background: #f5f5f5; color: #1f2a44; font-family: Arial, Helvetica, sans-serif; }
        .odoo-topbar { margin: -1.5rem -1.5rem 0; }
        .content > section { padding-bottom: 0 !important; }
        .odoo-list-page { background: #fff; min-height: calc(100vh - 44px); }
        .odoo-list-header {
            align-items: start;
            border-bottom: 1px solid #d9dde4;
            display: grid;
            grid-template-columns: 220px 1fr auto;
            gap: 8px;
            padding: 6px 8px 10px;
        }
        .odoo-list-header h1 { font-size: 21px; font-weight: 400; margin: 0 0 10px; }
        .odoo-primary {
            background: #7e57a3;
            border: 1px solid #7e57a3;
            color: #fff;
            display: inline-block;
            font-size: 13px;
            padding: 6px 15px;
            text-decoration: none;
        }
        .odoo-search-panel { justify-self: end; max-width: 945px; width: 100%; }
        .odoo-search { display: flex; }
        .odoo-search input { border: 1px solid #cfd3da; flex: 1; font-size: 13px; height: 30px; padding: 4px 7px; }
        .odoo-search button { background: #fff; border: 1px solid #cfd3da; border-left: 0; width: 30px; }
        .odoo-toolbar-line { background: #f4f5f7; display: flex; gap: 18px; font-size: 13px; padding: 6px 8px; width: fit-content; }
        .odoo-pager { align-items: center; display: flex; gap: 13px; font-size: 13px; padding-top: 34px; }
        .odoo-pager a { color: #526071; padding: 6px 8px; text-decoration: none; }
        .odoo-pager a.active { background: #d8dde4; color: #111827; }
        .odoo-directory { display: grid; grid-template-columns: 212px 1fr; }
        .odoo-departments { border-right: 1px solid #d9dde4; min-height: calc(100vh - 126px); padding: 16px 8px; }
        .odoo-sidebar-title { color: #344052; font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .odoo-departments a {
            color: #111827;
            display: flex;
            font-size: 13px;
            justify-content: space-between;
            padding: 5px 15px;
            text-decoration: none;
        }
        .odoo-departments a.active { background: #ccecf7; font-weight: 700; }
        .odoo-employees { background: #f7f8fa; padding: 12px 16px 20px; }
        .odoo-card-grid { display: grid; gap: 8px 16px; grid-template-columns: repeat(5, minmax(250px, 1fr)); }
        .odoo-employee-card {
            background: #fff;
            border: 1px solid #d9dde4;
            display: grid;
            grid-template-columns: 96px 1fr;
            min-height: 116px;
            position: relative;
        }
        .odoo-card-photo { background: #d9dde4; color: #fff; display: grid; font-size: 26px; font-weight: 700; place-items: center; text-decoration: none; }
        .odoo-card-photo img { height: 100%; object-fit: cover; width: 100%; }
        .odoo-card-copy { font-size: 12px; line-height: 1.55; min-width: 0; padding: 8px 20px 8px 16px; }
        .odoo-card-copy > div {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .odoo-card-head { align-items: center; display: flex; justify-content: space-between; gap: 8px; min-width: 0; }
        .odoo-card-head a {
            color: #07152d;
            display: inline-block;
            font-size: 14px;
            max-width: calc(100% - 16px);
            overflow: hidden;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .odoo-job { color: #20304b; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .odoo-tags { color: #20304b; min-height: 15px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .odoo-dot { background: #f5a400; border-radius: 50%; display: inline-block; flex: 0 0 11px; height: 11px; width: 11px; }
        .odoo-dot.online { background: #00b94f; }
        .red-dot, .blue-dot { border-radius: 50%; display: inline-block; height: 6px; margin: 0 4px 1px 0; width: 6px; }
        .red-dot { background: #f35f6b; }
        .blue-dot { background: #36a3e8; margin-left: 5px; }
        .odoo-card-clock { bottom: 5px; color: #ccd1d8; position: absolute; right: 8px; }
        .odoo-card-chat { bottom: 5px; color: #714e92; position: absolute; right: 28px; }
        .odoo-table { background: #fff; border-collapse: collapse; font-size: 13px; width: 100%; }
        .odoo-table th { background: #e9ecef; color: #3a4256; font-weight: 700; }
        .odoo-table td, .odoo-table th { border: 1px solid #dfe3e8; padding: 6px 8px; }
        .odoo-table a { color: #4f2f86; text-decoration: none; }
        .odoo-empty { color: #667085; padding: 40px; }
        @media (max-width: 1500px) { .odoo-card-grid { grid-template-columns: repeat(4, minmax(250px, 1fr)); } }
        @media (max-width: 1150px) { .odoo-card-grid { grid-template-columns: repeat(2, minmax(250px, 1fr)); } }
        @media (max-width: 800px) {
            .odoo-list-header, .odoo-directory { grid-template-columns: 1fr; }
            .odoo-search-panel { justify-self: stretch; }
            .odoo-pager { padding-top: 0; }
            .odoo-departments { min-height: 0; }
            .odoo-card-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush
