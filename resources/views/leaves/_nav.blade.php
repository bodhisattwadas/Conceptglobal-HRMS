<nav class="v15-topbar">
        <div class="v15-left">
            @php($mitchellLeaveId = \App\Models\LeaveRequest::whereHas('employee', fn ($query) => $query->where('first_name', 'Mitchell')->where('last_name', 'Admin'))->first()?->id)
            <a href="{{ route('leaves.requests') }}" class="v15-title"><i class="bi bi-grid-3x3-gap-fill"></i> {{ $appTitle ?? 'Leaves' }}</a>
            <a href="{{ $mitchellLeaveId ? route('leaves.requests.show', $mitchellLeaveId) : route('leaves.requests') }}">My Time Off</a>
            <a href="{{ route('leaves.types') }}">Overview</a>
            <a href="{{ route('leaves.requests') }}">Approvals</a>
            <a href="#">Reporting</a>
            <a href="{{ route('leaves.settings') }}">Configuration</a>
            <a href="#">Flight Tickets</a>
        </div>
</nav>

@once
    @push('styles')
        <style>
            .v15-topbar {
                align-items: center;
                background: #7e57a3;
                color: #fff;
                display: flex;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 13px;
                height: 45px;
                justify-content: space-between;
                margin: -1.5rem -1.5rem 0;
                padding: 0 15px;
            }
            .v15-topbar a { color: #fff; text-decoration: none; }
            .v15-left, .v15-right { align-items: center; display: flex; gap: 26px; }
            .v15-title { font-size: 20px; }
            .v15-right span { position: relative; }
            .v15-right b {
                background: #00a09d;
                border-radius: 8px;
                font-size: 10px;
                left: 11px;
                line-height: 1;
                padding: 2px 6px;
                position: absolute;
                top: -9px;
            }
            .v15-avatar {
                align-items: center;
                background: #b88762;
                border-radius: 50%;
                display: inline-flex;
                font-size: 10px;
                height: 24px;
                justify-content: center;
                width: 24px;
            }
            .oh-page-title {
                align-items: center;
                background: #fff;
                border-bottom: 1px solid #d9d9d9;
                display: flex;
                justify-content: space-between;
                padding: 13px 16px 8px;
            }
            .oh-page-title h1 {
                color: #6b4f94;
                font-size: 18px;
                font-weight: 400;
                margin: 0;
            }
            .oh-actions {
                align-items: center;
                background: #fff;
                border-bottom: 1px solid #d9d9d9;
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                padding: 6px 16px;
            }
            .btn-oh {
                --bs-btn-bg: #7e57a3;
                --bs-btn-border-color: #7e57a3;
                --bs-btn-color: #fff;
                --bs-btn-hover-bg: #6d4894;
                --bs-btn-hover-border-color: #6d4894;
                border-radius: 2px;
                padding: 6px 11px;
            }
            .btn-oh-light {
                background: #fff;
                border: 1px solid #d5d9e0;
                border-radius: 2px;
                color: #111827;
                padding: 6px 11px;
            }
            .oh-pattern {
                background-color: #f4f4f4;
                background-image: radial-gradient(#d9d9d9 .65px, transparent .65px);
                background-size: 3px 3px;
                border-top: 1px solid #e0e0e0;
                min-height: calc(100vh - 224px);
                padding: 14px 0;
            }
            .oh-sheet {
                background: #fff;
                border: 1px solid #cfd4dd;
                box-shadow: 0 1px 2px rgba(0,0,0,.08);
                margin: 0 auto;
                max-width: 1140px;
                min-height: 330px;
                padding: 32px;
            }
            .oh-row {
                display: grid;
                grid-template-columns: 150px 1fr 150px 1fr;
                gap: 12px;
                margin-bottom: 12px;
            }
            .oh-label {
                color: #111827;
                font-weight: 700;
            }
            .oh-value {
                border-left: 1px solid #d9dde6;
                color: #4f2f86;
                min-height: 24px;
                padding-left: 10px;
            }
            .oh-searchbar {
                align-items: center;
                display: flex;
                gap: 12px;
            }
            .oh-searchbar input {
                border: 0;
                border-bottom: 1px solid #7e57a3;
                border-radius: 0;
                width: 420px;
            }
            .oh-list-table {
                background: #fff;
                border-collapse: collapse;
                width: 100%;
            }
            .oh-list-table th {
                background: #e9ecef;
                color: #334155;
                font-weight: 700;
            }
            .oh-list-table td,
            .oh-list-table th {
                border: 1px solid #e0e4ea;
                padding: 5px 8px;
            }
            .oh-statusbar {
                display: flex;
                justify-content: flex-end;
            }
            .oh-state {
                color: #6b7280;
                padding: 8px 14px;
                position: relative;
            }
            .oh-state.active {
                background: #7e57a3;
                color: #fff;
            }
            .oh-chatter {
                background: #fff;
                margin: 0 auto;
                max-width: 1140px;
                padding: 18px 22px;
            }
            .oh-message {
                background: #fafafa;
                border: 1px solid #f0f0f0;
                margin-top: 14px;
                padding: 10px;
            }
            .text-purple {
                color: #6e36a2;
            }
            @media (max-width: 900px) {
                .v15-topbar { height: auto; flex-wrap: wrap; gap: 8px; padding: 8px; }
                .v15-left { flex-wrap: wrap; gap: 12px; }
                .v15-right { display: none; }
                .oh-actions, .oh-row { grid-template-columns: 1fr; }
            }
        </style>
    @endpush
@endonce
