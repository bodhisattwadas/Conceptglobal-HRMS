@section('module_nav')
    <nav class="oh-topbar">
        <div class="oh-app-title"><i class="bi bi-grid-3x3-gap-fill"></i> Attendances</div>
        <a href="{{ route('attendance.check') }}">Check In / Check Out</a>
        <a href="{{ route('attendance.check') }}">Kiosk Mode</a>
        <a href="{{ route('attendance.records') }}">Attendances</a>
        <a href="{{ route('attendance.regularization.show', \App\Models\AttendanceRegularizationRequest::first()?->id ?? 1) }}">Attendance Regularization</a>
        <a href="#">Reporting</a>
        <a href="#"><i class="bi bi-plus-lg"></i></a>
        <div class="oh-spacer"></div>
        <span><i class="bi bi-chat-left-text"></i><span class="oh-badge">1</span></span>
        <a href="#"><i class="bi bi-bell-fill"></i></a>
        <span><i class="bi bi-calendar-event"></i><span class="oh-badge">18</span></span>
        <a href="#">My Company (San Francisco)</a>
        <span class="fw-semibold">Mitchell Admin</span>
    </nav>
@endsection
