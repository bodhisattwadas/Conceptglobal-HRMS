<nav class="hr-admin-topbar">
    <div class="hr-admin-nav-left">
        <a href="{{ route('hr-admin.departments.index') }}" class="hr-admin-app"><i class="bi bi-grid-3x3-gap-fill"></i> HR Admin</a>
        <a href="{{ route('hr-admin.employees.index') }}">Employees</a>
        <a href="{{ route('hr-admin.departments.index') }}">Departments</a>
        <a href="{{ route('hr-admin.announcements.create') }}">Announcements</a>
        <a href="{{ route('hr-admin.transfers.create') }}">Transfers</a>
        <a href="{{ route('hr-admin.resignations.show') }}">Resignation</a>
        <a href="#">Configuration</a>
    </div>
</nav>

@once
    @push('styles')
        <style>
            .hr-admin-topbar {
                align-items: center;
                background: #7e57a3;
                color: #fff;
                display: flex;
                font-size: 13px;
                justify-content: space-between;
                margin: -1.5rem -1.5rem 0;
                min-height: 32px;
                padding: 0 10px;
            }
            .hr-admin-topbar a { color: #fff; text-decoration: none; }
            .hr-admin-nav-left, .hr-admin-nav-right { align-items: center; display: flex; gap: 18px; }
            .hr-admin-app { font-weight: 700; }
            .hr-admin-nav-right span { position: relative; }
            .hr-admin-nav-right b {
                background: #00a09d;
                border-radius: 8px;
                font-size: 10px;
                left: 9px;
                padding: 1px 5px;
                position: absolute;
                top: -10px;
            }
            .hr-admin-avatar {
                align-items: center;
                background: #b78b6a;
                border-radius: 50%;
                display: inline-flex;
                font-size: 10px;
                height: 22px;
                justify-content: center;
                width: 22px;
            }
            .hr-admin-page { background: #f4f5f7; min-height: calc(100vh - 122px); }
            .hr-title { color: #34234d; font-size: 16px; font-weight: 400; margin: 0 0 6px; }
            .hr-primary, .hr-secondary {
                background: #7e57a3;
                border: 1px solid #7e57a3;
                color: #fff;
                display: inline-block;
                font-size: 12px;
                line-height: 1;
                padding: 7px 11px;
                text-decoration: none;
            }
            .hr-secondary { background: #fff; border-color: #d8dde6; color: #111827; }
            .hr-toolbar {
                align-items: start;
                display: grid;
                grid-template-columns: 210px 1fr auto;
                gap: 10px;
                padding: 8px 10px;
            }
            .hr-search { justify-self: end; max-width: 620px; width: 100%; }
            .hr-search form { display: flex; }
            .hr-search input { border: 1px solid #cfd3da; flex: 1; font-size: 12px; height: 25px; padding: 3px 7px; }
            .hr-search button { background: #fff; border: 1px solid #cfd3da; border-left: 0; width: 28px; }
            .hr-tools { background: #f0f1f4; display: flex; gap: 14px; font-size: 12px; padding: 5px 7px; width: fit-content; }
            .hr-pager { align-items: center; display: flex; gap: 12px; font-size: 12px; padding-top: 25px; }
            .hr-view { background: #d8dde4; color: #111827; padding: 5px 8px; }
            .hr-pattern {
                background-color: #f4f4f4;
                background-image: radial-gradient(#d3d3d3 .6px, transparent .6px);
                background-size: 3px 3px;
                min-height: calc(100vh - 184px);
                padding: 10px 0;
            }
            .hr-form-card {
                background: #fff;
                border: 1px solid #c8ced8;
                box-shadow: 0 1px 8px rgba(15, 23, 42, .16);
                margin: 0 auto;
                max-width: 960px;
                min-height: 285px;
                padding: 30px 24px;
            }
            .hr-form-grid { display: grid; gap: 34px; grid-template-columns: 1fr 1fr; }
            .hr-field-grid { display: grid; grid-template-columns: 140px 1fr; row-gap: 9px; }
            .hr-field-grid label, .hr-label { color: #111827; font-weight: 700; }
            .hr-field-grid input, .hr-field-grid select, .hr-field-grid textarea, .hr-textarea {
                border: 1px solid #cfd4dc;
                border-radius: 2px;
                font-size: 12px;
                min-height: 24px;
                padding: 3px 6px;
                width: 100%;
            }
            .hr-field-grid input:focus, .hr-field-grid select:focus { background: #d9ccff; border-color: #7e57a3; outline: 0; }
            .hr-link-value { color: #6e36a2; padding-top: 3px; }
            .hr-stagebar { display: flex; justify-content: flex-end; margin-top: -29px; }
            .hr-stage { background: #f7f7f7; color: #6b7280; font-size: 11px; padding: 6px 14px; }
            .hr-stage.active { background: #7e57a3; color: #fff; }
            .hr-tab { border: 1px solid #d8dde6; border-bottom: 0; color: #6e36a2; display: inline-block; font-size: 12px; margin-top: 26px; padding: 8px 14px; }
            .hr-textarea { display: block; height: 96px; resize: none; }
            @media (max-width: 1000px) {
                .hr-admin-topbar, .hr-admin-nav-left { flex-wrap: wrap; gap: 9px; padding: 7px; }
                .hr-admin-nav-right { display: none; }
                .hr-toolbar, .hr-form-grid, .hr-field-grid { grid-template-columns: 1fr; }
                .hr-pager { padding-top: 0; }
            }
        </style>
    @endpush
@endonce
