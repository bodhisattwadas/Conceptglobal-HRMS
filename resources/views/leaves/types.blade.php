@extends('layouts.openhrms', ['title' => 'Time Off Types'])
@include('leaves._nav', ['appTitle' => 'Time Off'])

@section('content')
    <div class="leave-list-head">
        <div class="d-flex gap-2">
            <div>
                <h1>Time Off Types</h1>
                <button class="btn btn-oh">Create</button>
                <a href="{{ route('leaves.requests.create') }}" class="btn btn-oh-light"><i class="bi bi-download"></i></a>
            </div>
        </div>
        <div class="leave-search-block">
            <form class="leave-search">
                <input placeholder="Search...">
                <button><i class="bi bi-search"></i></button>
            </form>
            <div class="leave-tools">
                <span><i class="bi bi-funnel"></i> Filters</span>
                <span><i class="bi bi-stack"></i> Group By</span>
                <span><i class="bi bi-star"></i> Favorites</span>
            </div>
        </div>
        <div class="leave-pager">
            <span>{{ $types->firstItem() ?? 0 }}-{{ $types->lastItem() ?? 0 }} / {{ $types->total() }}</span>
            <i class="bi bi-chevron-left"></i>
            <i class="bi bi-chevron-right"></i>
            <span class="view-active"><i class="bi bi-list-ul"></i></span>
            <i class="bi bi-grid-3x3-gap-fill"></i>
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
@endsection

@push('styles')
    <style>
        body { background: #f5f6f8; font-family: Arial, Helvetica, sans-serif; }
        .leave-list-head {
            align-items: start;
            background: #fff;
            display: grid;
            gap: 12px;
            grid-template-columns: 240px 1fr auto;
            padding: 10px 16px 9px;
        }
        .leave-list-head h1 { font-size: 20px; font-weight: 400; margin: 0 0 11px; }
        .leave-search-block { justify-self: end; max-width: 668px; width: 100%; }
        .leave-search { display: flex; }
        .leave-search input { border: 1px solid #cfd3da; flex: 1; height: 31px; padding: 4px 7px; }
        .leave-search button { background: #fff; border: 1px solid #cfd3da; border-left: 0; width: 32px; }
        .leave-tools { background: #f5f6f8; display: flex; gap: 20px; padding: 7px 9px; width: fit-content; }
        .leave-pager { align-items: center; display: flex; gap: 18px; font-size: 13px; padding-top: 39px; }
        .view-active { background: #d8dde4; padding: 7px 9px; }
        .oh-list-table { font-size: 14px; }
        .oh-list-table th { height: 31px; }
        .oh-list-table td { height: 28px; }
        @media (max-width: 900px) { .leave-list-head { grid-template-columns: 1fr; } .leave-search-block { justify-self: stretch; } .leave-pager { padding-top: 0; } }
    </style>
@endpush
