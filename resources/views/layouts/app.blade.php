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
            <a href="#" class="disabled opacity-50">
                <i class="bi bi-cash-stack"></i>
                Payroll
            </a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">{{ $heading ?? 'Dashboard' }}</h1>
                <div class="text-secondary small">{{ $subheading ?? 'Core HR migration foundation' }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light border">MySQL</span>
                <span class="badge text-bg-danger">Bootstrap</span>
            </div>
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
