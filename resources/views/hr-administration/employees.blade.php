@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Employees'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div><h1 class="hr-title">Employees</h1><a href="{{ route('employees.create') }}" class="hr-primary">Create</a></div>
            <div class="hr-search">
                <form>
                    @foreach (request()->except('search', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input name="search" value="{{ request('search') }}" placeholder="Search...">
                    <button><i class="bi bi-search"></i></button>
                </form>
                <div class="hr-tools"><span><i class="bi bi-funnel-fill"></i> Filters</span><span><i class="bi bi-list"></i> Group By</span><span><i class="bi bi-star-fill"></i> Favorites</span></div>
            </div>
            <div class="hr-pager"><span>{{ $employees->firstItem() }}-{{ $employees->lastItem() }} / {{ $employees->total() }}</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i><span class="hr-view"><i class="bi bi-grid-3x3-gap-fill"></i></span><i class="bi bi-list-ul"></i><i class="bi bi-clock"></i></div>
        </div>
        <div class="hr-employee-shell">
            <aside class="hr-employee-filter">
                <div class="filter-title"><i class="bi bi-people-fill"></i> DEPARTMENT</div>
                <a href="{{ route('hr-admin.employees.index', request()->except('department_id', 'page')) }}" @class(['active' => !request('department_id')])><span>All</span></a>
                @foreach ($departments as $department)
                    <a href="{{ route('hr-admin.employees.index', [...request()->except('page'), 'department_id' => $department->id]) }}" @class(['active' => (int) request('department_id') === $department->id])><span>{{ $department->name }}</span><b>{{ $departmentCounts[$department->id] ?? 0 }}</b></a>
                @endforeach
            </aside>
            <main class="hr-employee-grid">
                @foreach ($employees as $employee)
                    <article class="hr-employee-card">
                        <div class="employee-photo">
                            @if ($employee->profile_photo_url)
                                <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                            @else
                                {{ $employee->initials }}
                            @endif
                        </div>
                        <div class="employee-copy">
                            <div class="employee-card-title"><a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a><i @class(['status-dot', 'online' => $employee->full_name === 'Mitchell Admin'])></i></div>
                            <div class="job">{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</div>
                            <div class="tags"><span></span>{{ $employee->workInformation?->employment_type ?? 'Employee' }}</div>
                            <div>{{ $employee->workInformation?->email ?? $employee->email }}</div>
                            <div>{{ $employee->workInformation?->work_mobile ?? $employee->phone }}</div>
                        </div>
                        <i class="bi bi-clock card-clock"></i>
                    </article>
                @endforeach
            </main>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .hr-employee-shell { display: grid; grid-template-columns: 180px 1fr; }
        .hr-employee-filter { background: #fff; border-right: 1px solid #d8dde6; min-height: calc(100vh - 190px); padding: 10px 7px; }
        .filter-title { color: #344052; font-size: 12px; font-weight: 700; margin-bottom: 8px; }
        .hr-employee-filter a {
            align-items: center;
            color: #111827;
            display: grid;
            font-size: 11px;
            gap: 8px;
            grid-template-columns: minmax(0, 1fr) auto;
            padding: 5px 14px;
            text-decoration: none;
        }
        .hr-employee-filter a span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .hr-employee-filter a b {
            min-width: 16px;
            text-align: right;
        }
        .hr-employee-filter a.active { background: #ccecf7; font-weight: 700; }
        .hr-employee-grid {
            align-items: start;
            display: grid;
            gap: 8px 12px;
            grid-auto-rows: 104px;
            grid-template-columns: repeat(4, minmax(230px, 1fr));
            padding: 10px;
        }
        .hr-employee-card {
            align-self: start;
            background: #fff;
            border: 1px solid #d8dde6;
            display: grid;
            grid-template-columns: 84px 1fr;
            height: 104px;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }
        .employee-photo { background: #d8dde6; color: #fff; display: grid; font-size: 23px; font-weight: 700; place-items: center; }
        .employee-photo img { height: 100%; object-fit: cover; width: 100%; }
        .employee-copy { font-size: 11px; line-height: 1.55; min-width: 0; padding: 7px 18px 7px 12px; }
        .employee-copy > div {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .employee-card-title { display: flex; justify-content: space-between; }
        .employee-card-title a {
            color: #07152d;
            display: inline-block;
            font-size: 12px;
            max-width: calc(100% - 14px);
            overflow: hidden;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .job { font-size: 12px; }
        .employee-copy .job {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tags span { background: #f35f6b; border-radius: 50%; display: inline-block; height: 5px; margin-right: 4px; width: 5px; }
        .status-dot { background: #f5a400; border-radius: 50%; height: 9px; width: 9px; }
        .status-dot.online { background: #00b94f; }
        .card-clock { bottom: 4px; color: #cfd4dc; position: absolute; right: 7px; }
        .hr-employee-card .employee-copy div:nth-child(4),
        .hr-employee-card .employee-copy div:nth-child(5) {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 1300px) { .hr-employee-grid { grid-template-columns: repeat(3, minmax(230px, 1fr)); } }
        @media (max-width: 900px) { .hr-employee-shell { grid-template-columns: 1fr; } .hr-employee-grid { grid-template-columns: 1fr; } }
    </style>
@endpush
