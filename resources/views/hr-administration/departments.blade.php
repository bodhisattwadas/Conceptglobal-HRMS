@extends('layouts.app', ['heading' => 'HR Administration', 'subheading' => 'Departments'])

@section('content')
    @include('hr-administration._topbar')
    <div class="hr-admin-page">
        <div class="hr-toolbar">
            <div>
                <h1 class="hr-title">Departments</h1>
                <a href="#" class="hr-primary">Create</a>
            </div>
            <div class="hr-search">
                <form>
                    <input name="search" value="{{ request('search') }}" placeholder="Search...">
                    <button><i class="bi bi-search"></i></button>
                </form>
                <div class="hr-tools"><span><i class="bi bi-funnel-fill"></i> Filters</span><span><i class="bi bi-list"></i> Group By</span><span><i class="bi bi-star-fill"></i> Favorites</span></div>
            </div>
            <div class="hr-pager"><span>1-5 / 5</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i><span class="hr-view"><i class="bi bi-grid-3x3-gap-fill"></i></span><i class="bi bi-list-ul"></i></div>
        </div>
        <div class="hr-department-grid">
            @foreach ($departments as $department)
                <article class="hr-department-card">
                    <div class="card-title-row"><h2>{{ $department['name'] }}</h2><span>{{ $department['employees'] }}</span></div>
                    <div class="card-body-row">
                        <a class="hr-primary" href="{{ route('hr-admin.employees.index') }}">Employees</a>
                        <div class="department-stats">
                            @if ($department['time_off']) <div><span>Time Off Requests</span><b>{{ $department['time_off'] }}</b></div> @endif
                            @if ($department['allocation']) <div><span>Allocation Requests</span><b>{{ $department['allocation'] }}</b></div> @endif
                            @if ($department['applicants']) <div><span>New Applicants</span><b>{{ $department['applicants'] }}</b></div> @endif
                        </div>
                    </div>
                    <div class="absence-row">
                        <span>Absence</span>
                        <div class="absence-track"><i style="width: {{ $department['absence_total'] ? ($department['absence_current'] / $department['absence_total']) * 100 : 0 }}%"></i></div>
                        <span>{{ $department['absence_current'] }} / {{ $department['absence_total'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .hr-department-grid { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(210px, 1fr)); padding: 0 10px; }
        .hr-department-card { background: #fff; border: 1px solid #d8dde6; min-height: 104px; padding: 10px; }
        .card-title-row { display: flex; justify-content: space-between; }
        .card-title-row h2 { color: #7e57a3; font-size: 13px; font-weight: 500; margin: 0; }
        .card-title-row span { font-size: 12px; font-weight: 700; }
        .card-body-row { align-items: start; display: flex; justify-content: space-between; margin-top: 16px; }
        .department-stats { color: #6e36a2; font-size: 11px; min-width: 120px; }
        .department-stats div { display: flex; justify-content: space-between; }
        .absence-row { align-items: center; border-top: 1px solid #eef0f4; color: #7e57a3; display: grid; font-size: 11px; grid-template-columns: 55px 1fr 35px; gap: 8px; margin-top: 16px; padding-top: 8px; }
        .absence-track { background: #edf0f3; height: 8px; }
        .absence-track i { background: #7e57a3; display: block; height: 100%; }
        @media (max-width: 1200px) { .hr-department-grid { grid-template-columns: repeat(2, minmax(210px, 1fr)); } }
        @media (max-width: 700px) { .hr-department-grid { grid-template-columns: 1fr; } }
    </style>
@endpush
