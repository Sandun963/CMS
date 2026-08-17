@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'New Requests', 'value' => $counts['new'], 'icon' => 'bi-file-earmark-plus', 'color' => 'primary'],
            ['label' => 'Assigned', 'value' => $counts['assigned'], 'icon' => 'bi-person-check', 'color' => 'warning'],
            ['label' => 'In Progress', 'value' => $counts['in_progress'], 'icon' => 'bi-hourglass-split', 'color' => 'info'],
            ['label' => 'Resolved', 'value' => $counts['resolved'], 'icon' => 'bi-check-circle', 'color' => 'success'],
            ['label' => 'Closed', 'value' => $counts['closed'], 'icon' => 'bi-folder-check', 'color' => 'secondary'],
            ['label' => 'Reopened', 'value' => $counts['reopened'], 'icon' => 'bi-arrow-repeat', 'color' => 'danger'],
        ];
    @endphp
    @foreach($cards as $c)
    <div class="col-md-2 col-6">
        <div class="card stat-card p-3 text-center h-100">
            <i class="bi {{ $c['icon'] }} text-{{ $c['color'] }} fs-3"></i>
            <div class="fs-4 fw-bold mt-1">{{ $c['value'] }}</div>
            <div class="text-muted small">{{ $c['label'] }}</div>
            <a href="{{ route('requests.index', ['status' => $c['label'] === 'New Requests' ? 'New' : $c['label']]) }}" class="stretched-link"></a>
        </div>
    </div>
    @endforeach
</div>

<div class="card stat-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Requests</strong>
        <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Request No.</th><th>Department</th><th>Title</th><th>Status</th><th>Assigned To</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($recent as $r)
                <tr>
                    <td><a href="{{ route('requests.show', $r) }}">{{ $r->request_number }}</a></td>
                    <td>{{ $r->department->name ?? '-' }}</td>
                    <td>{{ $r->title }}</td>
                    <td><span class="badge {{ $r->statusBadgeClass() }}">{{ $r->status }}</span></td>
                    <td>{{ $r->assignedTo->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
