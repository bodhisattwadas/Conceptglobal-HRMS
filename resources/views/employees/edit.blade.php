@extends('layouts.app', [
    'heading' => 'Edit Employee',
    'subheading' => $employee->full_name,
])

@section('content')
    @include('employees._module_nav')

    <div class="card table-card">
        <div class="card-body">
            <form method="post" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                @method('put')
                @include('employees._form', ['submitLabel' => 'Update Employee'])
            </form>
        </div>
    </div>
@endsection
