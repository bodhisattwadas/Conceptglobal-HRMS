@extends('layouts.openhrms', ['title' => 'My Time Off'])
@include('leaves._nav', ['appTitle' => 'Leaves'])

@section('content')
    <div class="oh-page-title">
        <h1>My Time Off</h1>
        <form class="oh-searchbar">
            <input class="form-control form-control-sm" placeholder="Search...">
            <i class="bi bi-search"></i>
        </form>
    </div>
    <div class="oh-actions">
        <div class="d-flex gap-2">
            <a href="{{ route('leaves.requests.create') }}" class="btn btn-oh">Create</a>
            <a href="{{ route('leaves.types') }}" class="btn btn-oh-light">Time Off Types</a>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-oh-light"><i class="bi bi-funnel"></i> Filters</button>
            <button class="btn btn-oh-light"><i class="bi bi-stack"></i> Group By</button>
        </div>
        <div class="text-end small text-secondary">{{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }} / {{ $requests->total() }}</div>
    </div>
    <table class="oh-list-table">
        <thead>
        <tr>
            <th>Employee</th>
            <th>Time Off Type</th>
            <th>Dates</th>
            <th>Duration</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($requests as $request)
            <tr>
                <td><a href="{{ route('leaves.requests.show', $request) }}">{{ $request->employee->full_name }}</a></td>
                <td>{{ $request->leaveType->name }}</td>
                <td>{{ $request->from_date->format('d/m/Y') }} - {{ $request->to_date->format('d/m/Y') }}</td>
                <td>{{ $request->duration_days }} Days ({{ $request->duration_hours }} Hours)</td>
                <td>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $requests->links() }}</div>
@endsection
