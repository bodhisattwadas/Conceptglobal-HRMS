<nav class="odoo-purple-nav timesheet-nav">
    <a href="{{ route('timesheets.index') }}" class="odoo-app-switcher"><i class="bi bi-grid-3x3-gap-fill"></i></a>
    <a href="{{ route('timesheets.index') }}" class="odoo-module-title">Timesheets</a>
    <a href="{{ route('timesheets.index') }}" @class(['active' => request()->routeIs('timesheets.index') || request()->routeIs('timesheets.show') || request()->routeIs('timesheets.create') || request()->routeIs('timesheets.edit')])>All Timesheets</a>
    <a href="{{ route('timesheets.reports.employee') }}" @class(['active' => request()->routeIs('timesheets.reports.*')])>Reporting</a>
    <a href="{{ route('timesheets.settings.edit') }}" @class(['active' => request()->routeIs('timesheets.settings.*')])>Configuration</a>
</nav>

@pushOnce('styles')
<style>
    .content > .p-4 { padding: 0 !important; }
    .topbar { display: none !important; }
    .odoo-purple-nav {
        align-items: center;
        background: #7f55a4;
        color: #fff;
        display: flex;
        min-height: 44px;
        overflow-x: auto;
        white-space: nowrap;
    }
    .odoo-purple-nav a {
        color: #fff;
        font-size: 13px;
        padding: 13px 14px;
        text-decoration: none;
    }
    .odoo-purple-nav a.active,
    .odoo-purple-nav a:hover { background: rgba(0,0,0,.1); }
    .odoo-purple-nav .odoo-module-title { font-size: 20px; padding-left: 2px; padding-right: 20px; }
    .timesheet-page { background: #f6f7f9; min-height: calc(100vh - 44px); }
    .timesheet-title { color: #6f4d95; font-size: 20px; padding: 12px 16px 6px; }
    .timesheet-toolbar {
        align-items: center;
        border-bottom: 1px solid #d7dce2;
        display: grid;
        gap: 10px;
        grid-template-columns: auto 1fr auto;
        padding: 0 16px 10px;
    }
    .btn-oh { background: #7f55a4; border-color: #7f55a4; color: #fff; border-radius: 0; }
    .btn-oh:hover { background: #6f4d95; border-color: #6f4d95; color: #fff; }
    .btn-oh-light { background: #fff; border: 1px solid #cfd6df; color: #111827; border-radius: 0; }
    .timesheet-search { display: flex; justify-content: end; min-width: 260px; }
    .timesheet-search input, .timesheet-search select {
        border: 1px solid #cfd6df;
        height: 30px;
        padding: 4px 8px;
    }
    .timesheet-controls { background: #fff; border-bottom: 1px solid #dfe3e8; display: flex; gap: 10px; padding: 6px 16px; }
    .timesheet-sheet {
        background: #fff;
        border: 1px solid #cfd6df;
        box-shadow: 0 3px 10px rgba(15,23,42,.08);
        margin: 12px auto;
        max-width: 1140px;
        padding: 28px 32px;
    }
    .timesheet-table { border-collapse: collapse; width: 100%; }
    .timesheet-table th { background: #e9ecef; color: #364154; font-weight: 700; }
    .timesheet-table th, .timesheet-table td { border: 1px solid #dfe3e8; padding: 7px 8px; vertical-align: top; }
    .timesheet-group { background: #f4f5f7; color: #2f3a56; font-weight: 700; }
    .timesheet-total { text-align: right; white-space: nowrap; }
    .ts-badge { border-radius: 999px; color: #fff; display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; }
    .ts-draft { background: #64748b; }
    .ts-submitted { background: #0ea5e9; }
    .ts-approved { background: #16a34a; }
    .ts-rejected { background: #dc2626; }
    .timesheet-form-grid { display: grid; gap: 14px 28px; grid-template-columns: 1fr 1fr; }
    .timesheet-field { display: grid; gap: 5px; }
    .timesheet-field label { font-weight: 700; }
    .timesheet-field input, .timesheet-field select, .timesheet-field textarea { border: 1px solid #cfd6df; min-height: 30px; padding: 4px 8px; }
    .progress-thin { background: #e5e7eb; height: 10px; width: 220px; }
    .progress-thin span { background: #7f55a4; display: block; height: 100%; }
    @media (max-width: 900px) {
        .timesheet-toolbar { grid-template-columns: 1fr; }
        .timesheet-search { justify-content: start; }
        .timesheet-form-grid { grid-template-columns: 1fr; }
    }
</style>
@endPushOnce
