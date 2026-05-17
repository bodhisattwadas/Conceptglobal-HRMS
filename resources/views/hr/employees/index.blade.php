@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Employees</h1>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Create</a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <input type="text" class="form-control w-50" placeholder="Search employees...">
        <div>
            <button class="btn btn-outline-secondary">Filters</button>
            <button class="btn btn-outline-secondary">Group By</button>
            <button class="btn btn-outline-secondary">Favorites</button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <button class="btn btn-outline-secondary">Card View</button>
            <button class="btn btn-outline-secondary">List View</button>
            <button class="btn btn-outline-secondary">Activity View</button>
        </div>
        <span>1-24 / 24</span>
    </div>

    <div class="row">
        <div class="col-md-3">
            <h5>DEPARTMENT</h5>
            <ul class="list-group">
                <li class="list-group-item">All</li>
                <li class="list-group-item">Administration</li>
                <li class="list-group-item">Management</li>
                <li class="list-group-item">Professional Services</li>
                <li class="list-group-item">Research & Development</li>
                <li class="list-group-item">Sales</li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="row">
                <!-- Employee cards will be dynamically loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection