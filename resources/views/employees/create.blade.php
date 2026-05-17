@extends('layouts.app', [
    'heading' => 'Add Employee',
    'subheading' => 'Create employee profile and first work assignment',
])

@section('content')
    @include('employees._module_nav')

    <div class="card table-card">
        <div class="card-body">
            <form method="post" action="{{ route('employees.store') }}">
                @include('employees._form', ['submitLabel' => 'Create Employee'])
            </form>
        </div>
    </div>
@endsection
