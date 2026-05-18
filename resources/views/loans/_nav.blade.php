<nav class="odoo-topbar">
    <div class="odoo-top-left">
        <a href="{{ route('loans.index') }}" class="odoo-app-switcher" aria-label="Apps">
            <i class="bi bi-grid-3x3-gap-fill"></i>
        </a>
        <a href="{{ route('loans.index') }}" class="odoo-module-title">Loans</a>
        <a href="{{ route('loans.index') }}" @class(['active' => request()->routeIs('loans.index')])>All Loans</a>
        <a href="{{ route('loans.create') }}" @class(['active' => request()->routeIs('loans.create')])>Request Loan</a>
        <a href="#">Reporting</a>
        <a href="#">Configuration</a>
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
            .odoo-top-left {
                align-items: center;
                display: flex;
                gap: 24px;
                min-width: 0;
            }
            .odoo-top-left a.active {
                font-weight: 700;
            }
            .odoo-app-switcher {
                font-size: 16px;
                margin-right: -12px;
            }
            .odoo-module-title {
                font-size: 20px;
                line-height: 1;
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
            }
        </style>
    @endpush
@endonce
