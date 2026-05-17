@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Kanban View</h1>
    <div class="row">
        @foreach ($employees as $employee)
        <div class="col-md-4 mb-4">
            <div class="card">
                <img src="{{ $employee->profile_photo_url }}" class="card-img-top" alt="{{ $employee->full_name }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $employee->full_name }}</h5>
                    <p class="card-text">{{ $employee->workInformation->jobPosition->name ?? 'N/A' }}</p>
                    <p class="card-text">{{ $employee->workInformation->department->name ?? 'N/A' }}</p>
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-primary">View Profile</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $employees->links() }}
</div>
@endsection