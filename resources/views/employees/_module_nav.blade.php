<nav class="odoo-topbar">
    <div class="odoo-top-left">
        <a href="{{ route('employees.index') }}" class="odoo-app-switcher" aria-label="Apps">
            <i class="bi bi-grid-3x3-gap-fill"></i>
        </a>
        <a href="{{ route('employees.index') }}" class="odoo-module-title">Employees</a>
        <a href="{{ route('employees.index') }}" @class(['active' => request()->routeIs('employees.*')])>Employees</a>
        <a href="{{ route('organization.index') }}">Departments</a>
    </div>
</nav>

@once
    @push('styles')
        <style>
            .odoo-topbar {
                align-items: center;
                background: #7e57a3;
                color: #fff;
                display: flex;
                font-size: 13px;
                height: 44px;
                justify-content: space-between;
                padding: 0 14px;
            }
            .odoo-topbar a {
                color: #fff;
                text-decoration: none;
            }
            .odoo-top-left,
            .odoo-top-right {
                align-items: center;
                display: flex;
                gap: 24px;
                min-width: 0;
            }
            .odoo-top-right {
                gap: 14px;
            }
            .odoo-app-switcher {
                font-size: 16px;
                margin-right: -12px;
            }
            .odoo-module-title {
                font-size: 20px;
                line-height: 1;
            }
            .odoo-icon-badge {
                position: relative;
            }
            .odoo-icon-badge b {
                background: #00a09d;
                border-radius: 8px;
                font-size: 10px;
                font-weight: 700;
                left: 9px;
                line-height: 1;
                padding: 2px 5px;
                position: absolute;
                top: -10px;
            }
            .odoo-user-pic {
                align-items: center;
                background: #b78b6a;
                border-radius: 50%;
                display: inline-flex;
                font-size: 10px;
                height: 24px;
                justify-content: center;
                width: 24px;
            }
            @media (max-width: 900px) {
                .odoo-topbar {
                    height: auto;
                    flex-wrap: wrap;
                    gap: 8px;
                    padding: 8px 12px;
                }
                .odoo-top-left {
                    gap: 12px;
                    flex-wrap: wrap;
                }
                .odoo-top-right {
                    display: none;
                }
            }
        </style>
    @endpush
@endonce
