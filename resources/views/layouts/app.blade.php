<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | IT Breakdown Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-red: #c0392b;
            --brand-navy: #1b2a4e;
        }
        body { background: #f4f6f9; }
        .sidebar {
            min-height: 100vh;
            color: #fff;
        }

        /* IT Head / Admin */
        .sidebar-admin {
            background: linear-gradient(180deg, #7a1830 0%, #941e3b 100%);
        }

        /* Assign Officer */
        .sidebar-assign {
            background: linear-gradient(180deg, #064b91 0%, #0877d1 100%);
        }

        /* Technical Officer */
        .sidebar-technical {
            background: linear-gradient(180deg, #075b32 0%, #0b8a4d 100%);
        }

        /* Ministry / Department User */
        .sidebar-ministry {
            background: linear-gradient(180deg, #3f1d73 0%, #6f38ad 100%);
        }

        /* Normal menu items */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.90);
            border-radius: 8px;
            margin-bottom: 6px;
            transition: all 0.2s ease;
        }

        /* Selected menu item */
        .sidebar .nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.15);
        }

        /* Mouse hover */
        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.10);
        }
        .sidebar-brand {
            font-weight: 700;
            font-size: 1.05rem;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar-logo {
            width: 200px;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
        }

        .sidebar-brand-text {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
        }
        .stat-card { border: none; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .badge-status { font-size: 0.75rem; }
        .table thead th { background: #eef1f6; font-size: 0.82rem; text-transform: uppercase; letter-spacing: .03em; }
        .navbar-top { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
<div class="d-flex">
    @php
    $sidebarClass = 'sidebar-admin';

    if(auth()->check()) {
        if(auth()->user()->isItHead()) {
            $sidebarClass = 'sidebar-admin';
        } elseif(auth()->user()->isAssignOfficer()) {
            $sidebarClass = 'sidebar-assign';
        } elseif(auth()->user()->isTechnicalOfficer()) {
            $sidebarClass = 'sidebar-technical';
        } elseif(auth()->user()->isMinistryUser()) {
            $sidebarClass = 'sidebar-ministry';
        }
    }
@endphp
    <nav class="sidebar {{ $sidebarClass }} p-3" style="width: 240px;">
        <div class="sidebar-brand pb-3 mb-3 text-center">
            <img src="{{ asset('images/logo.png') }}"
                alt="IT Department Logo"
                class="sidebar-logo mb-2">

            <div class="sidebar-brand-text">
                IT Department
            </div>
        </div>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            @auth
            @if(auth()->user()->isItHead())
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people me-2"></i>Manage Users</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}"><i class="bi bi-building me-2"></i>Manage Departments</a></li>
            @endif

            <li class="nav-item"><a class="nav-link {{ request()->routeIs('requests.index') ? 'active' : '' }}" href="{{ route('requests.index') }}"><i class="bi bi-file-earmark-text me-2"></i>{{ auth()->user()->isMinistryUser() ? 'My Requests' : 'All Requests' }}</a></li>

            @if(auth()->user()->isMinistryUser())
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('requests.create') ? 'active' : '' }}" href="{{ route('requests.create') }}"><i class="bi bi-plus-circle me-2"></i>Submit Request</a></li>
            @endif

            @if(auth()->user()->isItHead())
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart me-2"></i>Reports</a></li>
            @endif
            @endauth
        </ul>
    </nav>

    <div class="flex-grow-1">
        <nav class="navbar navbar-top px-4 py-2 d-flex justify-content-between">
            <div class="fw-semibold text-muted">@yield('title', 'Dashboard')</div>
            <div class="d-flex align-items-center gap-3">
                @auth
                <span class="text-muted small">
                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                    <span class="badge bg-light text-dark border ms-1">{{ auth()->user()->role->name }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
                @endauth
            </div>
        </nav>

        <main class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
