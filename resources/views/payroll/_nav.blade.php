<nav class="odoo-topbar">
    <div class="odoo-top-left">
        <a href="{{ route('payroll.structures.index') }}" class="odoo-app-switcher"><i class="bi bi-grid-3x3-gap-fill"></i></a>
        <a href="{{ route('payroll.structures.index') }}" class="odoo-module-title">Payroll</a>
        <a href="{{ route('payroll.contracts.index') }}" @class(['active' => request()->routeIs('payroll.contracts.*')])>Employee Payslips</a>
        <a href="{{ route('payroll.batches.index') }}" @class(['active' => request()->routeIs('payroll.batches.*')])>Payslips Batches</a>
        <a href="{{ route('payroll.structures.index') }}" @class(['active' => request()->routeIs('payroll.structures.*') || request()->routeIs('payroll.rules.*')])>Configuration</a>
        <a href="{{ route('payroll.rules.index') }}" @class(['active' => request()->routeIs('payroll.rules.*')])>Salary Rules</a>
        <a href="{{ route('payroll.registers.index') }}" @class(['active' => request()->routeIs('payroll.registers.*')])>Contribution Registers</a>
        <a href="{{ route('payroll.settings.edit') }}" @class(['active' => request()->routeIs('payroll.settings.*')])>Payroll Settings</a>
    </div>
</nav>

@once
@push('styles')
<style>
    .loan-title { color: #6e4c94; font-size: 32px; padding: 12px 16px 8px; }
    .loan-sheet { background: #fff; border: 1px solid #c8ced8; margin: 10px auto; max-width: 1140px; min-height: 320px; padding: 26px 32px; }
    .loan-sheet h2 { font-size: 40px; font-weight: 700; margin-bottom: 22px; }
    .loan-grid { display: grid; gap: 36px; grid-template-columns: 1fr 1fr; }
    .loan-fields { display: grid; grid-template-columns: 180px 1fr; row-gap: 8px; }
    .loan-fields label { font-weight: 700; padding-top: 5px; }
    .loan-fields.readonly span { border-left: 1px solid #d9dde6; color: #4f2f86; min-height: 24px; padding-left: 8px; padding-top: 5px; }
    .loan-table { border-collapse: collapse; width: 100%; background: #fff; }
    .loan-table th, .loan-table td { border: 1px solid #dfe3e8; padding: 7px 9px; }
    .loan-table th { background: #e9ecef; text-align: left; }
    .loan-tab { border: 1px solid #d8dde6; border-bottom: 0; color: #6e36a2; display: inline-block; margin-top: 18px; padding: 8px 12px; }
    .odoo-topbar { align-items: center; background: #7e57a3; color: #fff; display: flex; font-size: 13px; height: 44px; justify-content: space-between; padding: 0 14px; }
    .odoo-topbar a { color: #fff; text-decoration: none; }
    .odoo-top-left { align-items: center; display: flex; gap: 24px; min-width: 0; }
    .odoo-top-left a.active { font-weight: 700; }
    .odoo-app-switcher { font-size: 16px; margin-right: -12px; }
    .odoo-module-title { font-size: 20px; line-height: 1; }
    .btn-oh {
        background: #7e57a3;
        border: 1px solid #7e57a3;
        border-radius: 2px;
        color: #fff;
        font-size: 13px;
        line-height: 1;
        padding: 7px 11px;
    }
    .btn-oh:hover { background: #6f4b94; border-color: #6f4b94; color: #fff; }
    .btn-oh-light {
        background: #fff;
        border: 1px solid #d8dde6;
        border-radius: 2px;
        color: #111827;
        font-size: 13px;
        line-height: 1;
        padding: 7px 11px;
    }
</style>
@endpush
@endonce
