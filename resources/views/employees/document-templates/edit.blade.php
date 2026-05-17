@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Edit Document Template'])

@section('content')
    @include('employees._module_nav')
    <div class="card table-card">
        <div class="card-body">
            <form method="post" action="{{ route('employees.document-templates.update', $template) }}">
                @method('put')
                @include('employees.document-templates._form', ['submitLabel' => 'Update', 'template' => $template])
            </form>
        </div>
    </div>
@endsection
