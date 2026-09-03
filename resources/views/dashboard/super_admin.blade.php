@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Super Admin Dashboard</h3>
            <p class="text-muted mb-0">
                System-wide overview of users and breakdown requests.
            </p>
        </div>
    </div>

    {{-- User Statistics --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">
                            Total Users
                        </div>

                        <h3 class="fw-bold mb-0">
                            {{ $userCounts['total'] }}
                        </h3>
                    </div>

                    <div class="fs-2 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">
                            Active Users
                        </div>

                        <h3 class="fw-bold mb-0">
                            {{ $userCounts['active'] }}
                        </h3>
                    </div>

                    <div class="fs-2 text-success">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">
                            Inactive Users
                        </div>

                        <h3 class="fw-bold mb-0">
                            {{ $userCounts['inactive'] }}
                        </h3>
                    </div>

                    <div class="fs-2 text-danger">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Request Statistics --}}
    <div class="row g-3 mb-4">

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Total Requests
                    </div>

                    <h4 class="fw-bold mb-0">
                        {{ $requestCounts['total'] }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        New
                    </div>

                    <h4 class="fw-bold mb-0">
                        {{ $requestCounts['new'] }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        In Progress
                    </div>

                    <h4 class="fw-bold mb-0">
                        {{ $requestCounts['in_progress'] }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Resolved
                    </div>

                    <h4 class="fw-bold mb-0">
                        {{ $requestCounts['resolved'] }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        Closed
                    </div>

                    <h4 class="fw-bold mb-0">
                        {{ $requestCounts['closed'] }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        {{-- Recent Requests --}}
        <div class="col-lg-8">

            <div class="card stat-card">

                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">

                    <h5 class="mb-0 fw-semibold">
                        Recent Requests
                    </h5>

                    <a href="{{ route('requests.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        View All
                    </a>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead>
                            <tr>
                                <th>Request No.</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>

                            @forelse($recentRequests as $request)

                                <tr>

                                    <td class="fw-semibold">
                                        {{ $request->request_number }}
                                    </td>

                                    <td>
                                        {{ $request->title }}
                                    </td>

                                    <td>

                                        @php
                                            $badgeClass = match($request->status) {
                                                'New' => 'bg-primary',
                                                'Assigned' => 'bg-info text-dark',
                                                'In Progress' => 'bg-warning text-dark',
                                                'Resolved' => 'bg-success',
                                                'Closed' => 'bg-secondary',
                                                'Reopened' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }} badge-status">
                                            {{ $request->status }}
                                        </span>

                                    </td>

                                    <td>
                                        {{ $request->assignedTo?->name ?? 'Not Assigned' }}
                                    </td>

                                    <td>
                                        {{ $request->created_at?->format('d M Y') }}
                                    </td>

                                    <td>

                                        <a href="{{ route('requests.show', $request) }}"
                                           class="btn btn-sm btn-outline-secondary">

                                            <i class="bi bi-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6"
                                        class="text-center text-muted py-4">

                                        No breakdown requests found.

                                    </td>
                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        {{-- Recent Users --}}
        <div class="col-lg-4">

            <div class="card stat-card">

                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">

                    <h5 class="mb-0 fw-semibold">
                        Recent Users
                    </h5>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-sm btn-outline-primary">

                        Manage Users

                    </a>

                </div>

                <div class="card-body">

                    @forelse($recentUsers as $recentUser)

                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">

                            <div>

                                <div class="fw-semibold">
                                    {{ $recentUser->name }}
                                </div>

                                <div class="small text-muted">
                                    {{ $recentUser->username }}
                                </div>

                            </div>

                            <div class="text-end">

                                <span class="badge bg-light text-dark border">
                                    {{ $recentUser->role?->name ?? '-' }}
                                </span>

                                <div class="mt-1">

                                    @if($recentUser->is_active)

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="text-muted text-center py-3">
                            No users found.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

@endsection