@extends('layouts.openhrms', ['title' => 'Employees / '.$employee->full_name.' / Timesheets'])

@section('module_nav')
    @include('employees._module_nav')
@endsection

@section('content')
    <div class="odoo-timesheet-page">
        <div class="odoo-timesheet-title">Employees / {{ $employee->full_name }} / Timesheets</div>
        <div class="odoo-timesheet-header">
            <div>
                <button class="odoo-primary">Create</button>
                <button class="odoo-secondary icon"><i class="bi bi-download"></i></button>
            </div>
            <div class="odoo-timesheet-tools">
                <div class="odoo-search-chip"><b>Employee</b> {{ $employee->full_name }} <i class="bi bi-x"></i></div>
                <div class="odoo-search-chip"><i class="bi bi-funnel-fill"></i> <span>Date: February 2022</span> <i class="bi bi-x"></i></div>
                <input placeholder="Search...">
                <i class="bi bi-search"></i>
                <div class="tool-line"><span><i class="bi bi-funnel-fill"></i> Filters</span><span><i class="bi bi-list"></i> Group By</span><span><i class="bi bi-star-fill"></i> Favorites</span></div>
            </div>
            <div class="odoo-record-pager"><span>1-2 / 2</span><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i></div>
        </div>
        <table class="odoo-timesheet-table">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>Date <i class="bi bi-caret-down-fill"></i></th>
                    <th>Project</th>
                    <th>Task</th>
                    <th>Description</th>
                    <th class="text-end">Hours Spent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($timesheets as $row)
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['project'] }}</td>
                        <td>{{ $row['task'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td class="text-end">{{ $row['hours'] }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="blank"><td colspan="7"></td></tr>
                <tr class="total"><td colspan="6"></td><td>{{ $totalHours }}</td></tr>
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
    <style>
        body { background: #f5f6f8; font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
        .odoo-timesheet-page { background: #fff; min-height: calc(100vh - 44px); }
        .odoo-timesheet-title { color: #6e4c94; font-size: 18px; padding: 11px 15px; }
        .odoo-timesheet-header {
            align-items: start;
            display: grid;
            grid-template-columns: 210px 1fr auto;
            padding: 0 15px 10px;
        }
        .odoo-primary, .odoo-secondary {
            background: #7e57a3;
            border: 1px solid #7e57a3;
            color: #fff;
            display: inline-block;
            font-size: 13px;
            padding: 7px 12px;
        }
        .odoo-secondary { background: #fff; border-color: #d8dde6; color: #111827; }
        .odoo-secondary.icon { padding: 7px 9px; }
        .odoo-timesheet-tools {
            align-items: center;
            border: 1px solid #ccd1d8;
            display: flex;
            flex-wrap: wrap;
            min-height: 29px;
        }
        .odoo-search-chip { background: #eeeaf3; border-left: 4px solid #7e57a3; height: 24px; padding: 3px 6px; }
        .odoo-search-chip b { background: #7e57a3; color: #fff; margin: -3px 4px -3px -6px; padding: 4px 5px; }
        .odoo-timesheet-tools input { border: 0; flex: 1; min-width: 190px; outline: 0; padding: 4px; }
        .tool-line { background: #f4f5f7; flex-basis: 100%; display: flex; gap: 20px; padding: 6px 8px; }
        .odoo-record-pager { align-items: center; display: flex; gap: 16px; padding: 43px 0 0 16px; }
        .odoo-timesheet-table { border-collapse: collapse; width: 100%; }
        .odoo-timesheet-table th { background: #e9ecef; color: #3a4256; font-weight: 700; }
        .odoo-timesheet-table td, .odoo-timesheet-table th { border: 1px solid #dfe3e8; height: 28px; padding: 4px 7px; }
        .odoo-timesheet-table .blank td { background: #fff; height: 54px; }
        .odoo-timesheet-table .total td { background: #eee; border-color: #eee; font-weight: 700; height: 28px; text-align: right; }
        @media (max-width: 900px) { .odoo-timesheet-header { gap: 8px; grid-template-columns: 1fr; } .odoo-record-pager { padding: 0; } }
    </style>
@endpush
