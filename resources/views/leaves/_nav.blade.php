@section('module_nav')
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
        <div class="v15-right">
            <span><i class="bi bi-chat-fill"></i><b>5</b></span>
            <span><i class="bi bi-bell-fill"></i></span>
            <span><i class="bi bi-clock-history"></i><b>14</b></span>
            <a href="#">My Company (San Francisco)</a>
            <span class="v15-avatar">MA</span>
            <span>Mitchell Admin</span>
        </div>
    </nav>
@endsection

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
            @media (max-width: 900px) {
                .v15-topbar { height: auto; flex-wrap: wrap; gap: 8px; padding: 8px; }
                .v15-left { flex-wrap: wrap; gap: 12px; }
                .v15-right { display: none; }
            }
        </style>
    @endpush
@endonce
