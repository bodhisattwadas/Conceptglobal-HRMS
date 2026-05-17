@extends('layouts.app', ['heading' => 'Leaves', 'subheading' => 'Create Time Off'])


@section('content')
    @include('leaves._nav', ['appTitle' => 'Leaves'])

    <div class="oh-page-title"><h1>My Time Off / New</h1></div>
    <form method="post" action="{{ route('leaves.requests.store') }}">
        @csrf
        <div class="oh-actions">
            <div class="d-flex gap-2">
                <button class="btn btn-oh">Save</button>
                <a href="{{ route('leaves.requests') }}" class="btn btn-oh-light">Discard</a>
            </div>
            <button type="button" class="btn btn-oh-light"><i class="bi bi-gear-fill"></i> Action</button>
            <div></div>
        </div>
        <div class="oh-pattern">
            <div class="oh-sheet">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Time Off Type</label>
                        <select name="leave_type_id" class="form-select" required>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">From Date</label>
                        <input name="from_date" type="date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">To Date</label>
                        <input name="to_date" type="date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Company</label>
                        <select name="company_id" class="form-select">
                            <option value="">Select company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
