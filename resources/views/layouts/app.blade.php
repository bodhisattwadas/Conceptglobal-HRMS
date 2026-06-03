<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --hr-sidebar: #172133;
            --hr-accent: #6f5b9a;
            --hr-muted: #f4f6f9;
        }

        body {
            background: var(--hr-muted);
        }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 270px 1fr;
        }

        .sidebar {
            background: var(--hr-sidebar);
            color: #fff;
            padding: 1.25rem;
        }

        .sidebar a {
            color: rgba(255, 255, 255, .78);
            border-radius: .5rem;
            display: flex;
            gap: .65rem;
            padding: .75rem .85rem;
            text-decoration: none;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: .65rem;
            display: grid;
            place-items: center;
            background: var(--hr-accent);
            font-weight: 800;
        }

        .content {
            min-width: 0;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e7ebf0;
        }
        .app-user-tools {
            align-items: center;
            color: #334155;
            display: inline-flex;
            gap: 12px;
        }
        .app-user-tools i { color: #64748b; }
        .app-icon-badge {
            position: relative;
        }
        .app-icon-badge b {
            background: #00a09d;
            border-radius: 8px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            left: 10px;
            line-height: 1;
            padding: 2px 5px;
            position: absolute;
            top: -10px;
        }
        .app-user-chip {
            align-items: center;
            color: #0f172a;
            display: inline-flex;
            gap: 8px;
            font-weight: 600;
        }
        .app-avatar {
            align-items: center;
            background: #e2e8f0;
            border-radius: 50%;
            display: inline-flex;
            font-size: 11px;
            height: 28px;
            justify-content: center;
            width: 28px;
        }
        .sidebar-user-tools {
            align-items: center;
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
            padding: 0 .25rem;
        }
        .sidebar-user-tools .app-user-chip {
            color: #fff;
            font-size: 13px;
        }
        .sidebar-user-tools .app-icon-badge i {
            color: rgba(255,255,255,.95);
        }

        .metric {
            border: 0;
            border-radius: .75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .table-card {
            border: 0;
            border-radius: .75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }
        @media (max-width: 991.98px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="brand-mark">H</div>
            <div>
                <div class="fw-bold">Horilla HRMS</div>
                <div class="small text-white-50">Laravel migration</div>
            </div>
        </div>
        <div class="sidebar-user-tools">
            <span class="app-user-chip">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=64&h=64&fit=crop&crop=face" alt="Mitchell Admin" width="28" height="28" class="rounded-circle">
                Mitchell Admin
            </span>
        </div>

        <nav class="d-grid gap-1">
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="{{ route('organization.index') }}" @class(['active' => request()->routeIs('organization.*')])>
                <i class="bi bi-building"></i>
                Organization
            </a>
            <a href="{{ route('employees.index') }}" @class(['active' => request()->routeIs('employees.*')])>
                <i class="bi bi-people"></i>
                Employees
            </a>
            <a href="{{ route('hr-admin.departments.index') }}" @class(['active' => request()->routeIs('hr-admin.*')])>
                <i class="bi bi-person-workspace"></i>
                HR Administration
            </a>
            <a href="{{ route('attendance.check') }}" @class(['active' => request()->routeIs('attendance.*')])>
                <i class="bi bi-calendar-check"></i>
                Attendance
            </a>
            <a href="{{ route('leaves.types') }}" @class(['active' => request()->routeIs('leaves.*')])>
                <i class="bi bi-briefcase"></i>
                Leave
            </a>
            <a href="#">
                <i class="bi bi-chat-dots"></i>
                Communication
            </a>
            <a href="{{ route('loans.index') }}" @class(['active' => request()->routeIs('loans.*')])>
                <i class="bi bi-cash-coin"></i>
                Loans
            </a>
            <a href="{{ route('payroll.structures.index') }}" @class(['active' => request()->routeIs('payroll.*')])>
                <i class="bi bi-cash-stack"></i>
                Payroll
            </a>
            <a href="{{ route('timesheets.index') }}" @class(['active' => request()->routeIs('timesheets.*') || request()->routeIs('projects.tasks.*')])>
                <i class="bi bi-clock-history"></i>
                Timesheets
            </a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">{{ $heading ?? 'Dashboard' }}</h1>
                <div class="text-secondary small">{{ $subheading ?? 'Core HR migration foundation' }}</div>
            </div>
            <div></div>
        </header>

        <section class="p-4">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Please fix the highlighted fields.</div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
