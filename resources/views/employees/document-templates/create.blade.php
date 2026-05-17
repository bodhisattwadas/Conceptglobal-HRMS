@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Create Document Template'])

@section('content')
    @include('employees._module_nav')
    <div class="odoo-form-shell">
        <div class="odoo-form-card">
            <form method="post" action="{{ route('employees.document-templates.store') }}">
                @include('employees.document-templates._form', ['submitLabel' => 'Save', 'template' => null])
            </form>
        </div>
    </div>
@endsection
