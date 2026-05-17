@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Activity View</h1>
    <ul class="list-group">
        @foreach ($employees as $employee)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <h5>{{ $employee->full_name }}</h5>
                <p>{{ $employee->workInformation->jobPosition->name ?? 'N/A' }} - {{ $employee->workInformation->department->name ?? 'N/A' }}</p>
            </div>
            <span class="badge bg-primary rounded-pill">{{ $employee->updated_at->diffForHumans() }}</span>
        </li>
        @endforeach
    </ul>
    {{ $employees->links() }}
</div>
@endsection