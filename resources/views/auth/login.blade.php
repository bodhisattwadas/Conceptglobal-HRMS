<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <form method="post" action="{{ route('login.store') }}" class="card border-0 shadow-sm" style="width: min(420px, 100%);">
        @csrf
        <div class="card-body p-4">
            <h1 class="h4 mb-1">Concept HRMS</h1>
            <p class="text-secondary mb-4">Sign in to continue.</p>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control" required>
            </div>
            <label class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" value="1">
                <span class="form-check-label">Remember me</span>
            </label>
            <button class="btn btn-primary w-100">Login</button>
        </div>
    </form>
</main>
</body>
</html>
