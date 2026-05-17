@extends('layouts.app', ['heading' => 'Employees', 'subheading' => 'Document Templates'])

@section('content')
    @include('employees._module_nav')

    <div class="odoo-list-page doc-template-page">
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
                        <td class="text-end"><a href="{{ route('employees.document-templates.edit', $template) }}" class="btn btn-sm doc-edit-btn"><i class="bi bi-pencil"></i></a></td>
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

@push('styles')
    <style>
        .doc-template-page { background: #f5f6f9; border: 1px solid #d8dde6; }
        .odoo-list-header { align-items: start; background: #fff; border-bottom: 1px solid #d8dde6; display: grid; gap: 16px; grid-template-columns: 1fr auto auto; padding: 14px 16px; }
        .odoo-list-header h1 { color: #2d3748; font-size: 36px; font-weight: 400; margin: 0 0 8px; }
        .odoo-primary { background: #7e57a3; border: 1px solid #7e57a3; color: #fff; display: inline-block; padding: 7px 12px; text-decoration: none; }
        .odoo-primary:hover { background: #6f4b94; color: #fff; }
        .odoo-search { align-items: center; display: flex; }
        .odoo-search input { border: 1px solid #cfd4dc; height: 33px; min-width: 360px; padding: 6px 10px; }
        .odoo-search button { background: #fff; border: 1px solid #cfd4dc; border-left: 0; height: 33px; width: 40px; }
        .odoo-pager { color: #4b5563; padding-top: 7px; }
        .odoo-table { border-collapse: collapse; width: 100%; }
        .odoo-table th, .odoo-table td { border-bottom: 1px solid #e1e5eb; padding: 10px 12px; vertical-align: middle; }
        .odoo-table th { background: #eceff3; color: #374151; font-weight: 700; }
        .doc-edit-btn { border: 1px solid #d0d5dd; color: #475467; }
    </style>
@endpush
