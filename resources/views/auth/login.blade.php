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
                background-image: linear-gradient(
                rgba(255, 255, 255, 0.35),
                rgba(255, 255, 255, 0.35)
            ),
            url('{{ asset("images/bg.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .login-card { border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
        .login-logo {
                width: 280px;
                max-width: 100%;
                height: auto;
                object-fit: contain;
                border-radius: 12px;
            }

            .login-department {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 5px;
            }

            .login-system-name {
                font-size: 18px;
                color: #6c757d;
                margin-bottom: 0;
            }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
<div class="card login-card p-4" style="width: 350px;">
<div class="text-center mb-4">
    <img src="{{ asset('images/logo.png') }}"
         alt="Western Provincial Council"
         class="login-logo">

    <!-- <h3 class="login-department mt-3">IT Department</h3> -->

    <p class="login-system-name">
        Complain Management System
    </p>
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
    <strong>Demo accounts</strong> (password: <code>1234</code>)<br>
        IT Head: <code>ithead</code><br>
        Assign Officer: <code>assignofficer</code><br>
        Technical Officer A: <code>techofficer</code><br>
        Technical Officer B: <code>techofficerb</code><br>
        Department User: <code>deptuser</code><br>
        HR Department User: <code>hruser</code><br>
        Username: superadmin
    </div>
</div>
</body>
</html>
