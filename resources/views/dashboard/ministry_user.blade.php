@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Dashboard</h4>
    <a href="{{ route('requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Submit New Request</a>
</div>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'My Requests', 'value' => $counts['my_requests'], 'icon' => 'bi-file-earmark-text', 'color' => 'primary'],
            ['label' => 'Open', 'value' => $counts['open'], 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
            ['label' => 'Resolved', 'value' => $counts['resolved'], 'icon' => 'bi-check-circle', 'color' => 'success'],
            ['label' => 'Closed', 'value' => $counts['closed'], 'icon' => 'bi-folder-check', 'color' => 'secondary'],
        ];
    @endphp
    @foreach($cards as $c)
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3 text-center">
            <i class="bi {{ $c['icon'] }} text-{{ $c['color'] }} fs-3"></i>
            <div class="fs-4 fw-bold mt-1">{{ $c['value'] }}</div>
            <div class="text-muted small">{{ $c['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card stat-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>My Requests</strong>
        <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Request No.</th><th>Problem</th><th>Status</th><th>Submitted On</th></tr></thead>
            <tbody>
                @forelse($myRequests as $r)
                <tr>
                    <td><a href="{{ route('requests.show', $r) }}">{{ $r->request_number }}</a></td>
                    <td>{{ $r->title }}</td>
                    <td><span class="badge {{ $r->statusBadgeClass() }}">{{ $r->status }}</span>
                        @if($r->status === 'Resolved')
                            <span class="badge bg-light text-dark border">Awaiting Confirmation</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">You haven't submitted any requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
