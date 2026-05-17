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
            --oh-purple: #7c5aa6;
            --oh-purple-dark: #65478d;
            --oh-line: #d9dce3;
            --oh-text: #1f2940;
        }
        body {
            background: #f7f7f7;
            color: var(--oh-text);
            font-size: 14px;
        }
        .oh-topbar {
            align-items: center;
            background: var(--oh-purple);
            color: #fff;
            display: flex;
            min-height: 40px;
            padding: 0 16px;
        }
        .oh-topbar a {
            color: #fff;
            padding: 0 12px;
            text-decoration: none;
        }
        .oh-app-title {
            align-items: center;
            display: flex;
            font-size: 18px;
            gap: 10px;
            margin-right: 18px;
        }
        .oh-spacer {
            flex: 1;
        }
        .oh-badge {
            background: #00b894;
            border-radius: 2px;
            font-size: 11px;
            line-height: 1;
            padding: 2px 4px;
        }
        .oh-page-title {
            align-items: center;
            background: #fff;
            border-bottom: 1px solid var(--oh-line);
            display: flex;
            justify-content: space-between;
            padding: 10px 16px 8px;
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
            border-bottom: 1px solid var(--oh-line);
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            padding: 6px 16px;
        }
        .btn-oh {
            --bs-btn-bg: var(--oh-purple);
            --bs-btn-border-color: var(--oh-purple);
            --bs-btn-color: #fff;
            --bs-btn-hover-bg: var(--oh-purple-dark);
            --bs-btn-hover-border-color: var(--oh-purple-dark);
            border-radius: 2px;
            padding: 4px 10px;
        }
        .btn-oh-light {
            background: #fff;
            border: 1px solid #d5d9e0;
            border-radius: 2px;
            padding: 4px 10px;
        }
        .oh-pattern {
            background-color: #f4f4f4;
            background-image: radial-gradient(#d9d9d9 .65px, transparent .65px);
            background-size: 3px 3px;
            border-top: 1px solid #e0e0e0;
            min-height: calc(100vh - 126px);
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
            grid-template-columns: 160px 1fr 160px 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .oh-label {
            color: #111827;
            font-weight: 700;
        }
        .oh-value {
            border-left: 1px solid #d9dde6;
            color: #4f3d8f;
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
            border-bottom: 1px solid var(--oh-purple);
            border-radius: 0;
            width: 420px;
        }
        .oh-list-table {
            background: #fff;
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
            background: var(--oh-purple);
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
        @media (max-width: 900px) {
            .oh-topbar {
                flex-wrap: wrap;
                gap: 8px;
                padding: 8px;
            }
            .oh-actions {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .oh-row {
                grid-template-columns: 1fr;
            }
            .oh-value {
                border-left: 0;
                border-top: 1px solid #d9dde6;
                padding-left: 0;
                padding-top: 4px;
            }
            .oh-searchbar input {
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('module_nav')

    @if (session('status'))
        <div class="alert alert-success rounded-0 mb-0 py-2 px-3">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-0 mb-0 py-2 px-3">
            {{ $errors->first() }}
        </div>
    @endif

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
