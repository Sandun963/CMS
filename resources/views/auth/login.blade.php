<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | IT Breakdown Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1b2a4e 0%, #2c3e6b 100%);
            min-height: 100vh;
        }
        .login-card { border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
<div class="card login-card p-4" style="width: 380px;">
    <div class="text-center mb-4">
        <i class="bi bi-hdd-network fs-1 text-primary"></i>
        <h5 class="fw-bold mt-2 mb-0">IT BREAKDOWN</h5>
        <small class="text-muted">Management System</small>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 small">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label small fw-semibold">Username or Email</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <hr>
    <div class="small text-muted">
        <strong>Demo accounts</strong> (password: <code>password123</code>)<br>
        IT Head: <code>ithead</code><br>
        Assign Officer: <code>assignofficer</code><br>
        Technical Officer: <code>techofficer</code><br>
        Ministry User: <code>deptuser</code>
    </div>
</div>
</body>
</html>
