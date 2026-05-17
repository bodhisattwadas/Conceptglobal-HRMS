@section('module_nav')
    <nav class="oh-topbar">
        <div class="oh-app-title"><i class="bi bi-grid-3x3-gap-fill"></i> {{ $appTitle ?? 'Leaves' }}</div>
        <a href="{{ route('leaves.requests') }}">My Time Off</a>
        <a href="{{ route('leaves.types') }}">Overview</a>
        <a href="{{ route('leaves.requests') }}">Approvals</a>
        <a href="#">Reporting</a>
        <a href="{{ route('leaves.settings') }}">Configuration</a>
        <a href="#">Flight Tickets</a>
        <div class="oh-spacer"></div>
        <span><i class="bi bi-chat-left-text"></i><span class="oh-badge">5</span></span>
        <a href="#"><i class="bi bi-bell-fill"></i></a>
        <span><i class="bi bi-clock-history"></i><span class="oh-badge">14</span></span>
        <a href="#">My Company (San Francisco)</a>
        <span class="fw-semibold">Mitchell Admin</span>
    </nav>
@endsection
