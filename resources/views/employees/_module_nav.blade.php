<div class="employee-module-nav">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Employees
        </div>
        <a href="{{ route('employees.index') }}" @class(['active' => request()->routeIs('employees.*')])>Employees</a>
        <a href="#">Document Templates</a>
        <a href="{{ route('organization.index') }}">Departments</a>
        <a href="#">Legal Actions</a>
        <a href="#">Loans & Advances</a>
        <a href="#">Configuration</a>
    </div>
    <div class="d-none d-lg-flex align-items-center gap-3 small">
        <span><i class="bi bi-chat-dots-fill text-info"></i> 4</span>
        <span><i class="bi bi-bell-fill text-light"></i></span>
        <span>My Company</span>
        <span class="fw-semibold">Mitchell Admin</span>
    </div>
</div>

@once
    @push('styles')
        <style>
            .employee-module-nav {
                background: #6f5b9a;
                color: #fff;
                display: flex;
                justify-content: space-between;
                margin: -1.5rem -1.5rem 1rem;
                padding: .55rem .85rem;
            }
            .employee-module-nav a {
                color: rgba(255,255,255,.82);
                font-size: .82rem;
                text-decoration: none;
            }
            .employee-module-nav a.active,
            .employee-module-nav a:hover {
                color: #fff;
            }
        </style>
    @endpush
@endonce
