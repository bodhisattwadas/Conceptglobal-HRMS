@extends('layouts.openhrms', ['title' => 'Time Off Types'])
@include('leaves._nav', ['appTitle' => 'Time Off'])

@section('content')
    <div class="oh-page-title">
        <h1>Time Off Types</h1>
        <form class="oh-searchbar">
            <input class="form-control form-control-sm" placeholder="Search...">
            <i class="bi bi-search"></i>
        </form>
    </div>
    <div class="oh-actions">
        <div class="d-flex gap-2">
            <button class="btn btn-oh">Create</button>
            <a href="{{ route('leaves.requests.create') }}" class="btn btn-oh-light"><i class="bi bi-box-arrow-up-right"></i></a>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-oh-light"><i class="bi bi-funnel"></i> Filters</button>
            <button class="btn btn-oh-light"><i class="bi bi-stack"></i> Group By</button>
            <button class="btn btn-oh-light"><i class="bi bi-star"></i> Favorites</button>
        </div>
        <div class="text-end small text-secondary">
            {{ $types->firstItem() ?? 0 }}-{{ $types->lastItem() ?? 0 }} / {{ $types->total() }}
            &nbsp; <i class="bi bi-chevron-left"></i> &nbsp; <i class="bi bi-chevron-right"></i>
            &nbsp; <i class="bi bi-list-ul"></i>
        </div>
    </div>
    <table class="oh-list-table">
        <thead>
        <tr>
            <th style="width: 40px"><input type="checkbox"></th>
            <th style="width: 36px"></th>
            <th>Display Name</th>
            <th>Approval</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($types as $type)
            <tr>
                <td><input type="checkbox"></td>
                <td class="text-secondary"><i class="bi bi-chevron-expand"></i></td>
                <td>{{ $type->name }}</td>
                <td>{{ $type->approval }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="p-3">{{ $types->links() }}</div>
@endsection
