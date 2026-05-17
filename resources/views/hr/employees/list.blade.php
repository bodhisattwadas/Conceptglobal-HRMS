@extends('layouts.app')

@section('content')
<div class="container">
    <h1>List View</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Job Position</th>
                <th>Department</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
            <tr>
                <td><img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}" width="50"></td>
                <td>{{ $employee->full_name }}</td>
                <td>{{ $employee->workInformation->jobPosition->name ?? 'N/A' }}</td>
                <td>{{ $employee->workInformation->department->name ?? 'N/A' }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->phone }}</td>
                <td>
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $employees->links() }}
</div>
@endsection