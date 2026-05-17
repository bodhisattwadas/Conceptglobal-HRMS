@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Document Templates'])

@section('content')
    @include('employees._module_nav')

    <div class="odoo-list-page">
        <div class="odoo-list-header">
            <div>
                <h1>Document Templates</h1>
                <a href="{{ route('employees.document-templates.create') }}" class="odoo-primary">Create</a>
            </div>
            <div class="odoo-search-panel">
                <form method="get" class="odoo-search">
                    <input name="search" value="{{ request('search') }}" placeholder="Search...">
                    <button aria-label="Search"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="odoo-pager">
                <span>{{ $templates->firstItem() ?? 0 }}-{{ $templates->lastItem() ?? 0 }} / {{ $templates->total() }}</span>
            </div>
        </div>

        <table class="odoo-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td>{{ $template->code }}</td>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->description }}</td>
                        <td>{{ $template->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end"><a href="{{ route('employees.document-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No document templates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($templates->hasPages())
            <div class="p-3">{{ $templates->links() }}</div>
        @endif
    </div>
@endsection
