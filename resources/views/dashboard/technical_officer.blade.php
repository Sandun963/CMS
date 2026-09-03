@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'My Jobs', 'value' => $counts['my_jobs'], 'icon' => 'bi-briefcase', 'color' => 'primary'],
            ['label' => 'In Progress', 'value' => $counts['in_progress'], 'icon' => 'bi-hourglass-split', 'color' => 'info'],
            ['label' => 'Completed', 'value' => $counts['completed'], 'icon' => 'bi-check-circle', 'color' => 'success'],
            ['label' => 'Overdue', 'value' => $counts['overdue'], 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
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
        <strong>My Assigned Jobs</strong>
        <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Request No.</th><th>Division</th><th>Problem</th><th>Status</th><th>Due Date</th><th></th></tr></thead>
            <tbody>
                @forelse($myJobs as $job)
                <tr>
                    <td>{{ $job->assignment->request->request_number }}</td>
                    <td>{{ $job->assignment->request->division?->name ?? '-' }}</td>
                    <td>{{ $job->assignment->request->title }}</td>
                    <td><span class="badge {{ $job->assignment->request->statusBadgeClass() }}">{{ $job->assignment->request->status }}</span></td>
                    <td>{{ optional($job->due_date)->format('d/m/Y') ?? '-' }}</td>
                    <td><a href="{{ route('requests.show', $job->assignment->request) }}" class="btn btn-sm btn-primary">Open</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No jobs assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
