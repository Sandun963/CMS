@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<h4 class="mb-4">Reports</h4>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card stat-card p-3">
            <strong class="mb-2 d-block">Requests by Department</strong>
            <table class="table table-sm mb-0">
                <tbody>
                @forelse($byDepartment as $row)
                    <tr><td>{{ $row->department->name ?? 'Unknown' }}</td><td class="text-end fw-semibold">{{ $row->total }}</td></tr>
                @empty
                    <tr><td class="text-muted">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stat-card p-3">
            <strong class="mb-2 d-block">Requests by Status</strong>
            <table class="table table-sm mb-0">
                <tbody>
                @forelse($byStatus as $row)
                    <tr><td>{{ $row->status }}</td><td class="text-end fw-semibold">{{ $row->total }}</td></tr>
                @empty
                    <tr><td class="text-muted">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stat-card p-3">
            <strong class="mb-2 d-block">Requests by Category</strong>
            <table class="table table-sm mb-0">
                <tbody>
                @forelse($byCategory as $row)
                    <tr><td>{{ $row->category->name ?? 'Uncategorized' }}</td><td class="text-end fw-semibold">{{ $row->total }}</td></tr>
                @empty
                    <tr><td class="text-muted">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stat-card p-3">
            <strong class="mb-2 d-block">Requests by Technical Officer</strong>
            <table class="table table-sm mb-0">
                <tbody>
                @forelse($byTechnician as $row)
                    <tr><td>{{ $row->assignedTo->name ?? 'Unassigned' }}</td><td class="text-end fw-semibold">{{ $row->total }}</td></tr>
                @empty
                    <tr><td class="text-muted">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<p class="text-muted small mt-3">PDF / Excel export can be added with <code>barryvdh/laravel-dompdf</code> and <code>maatwebsite/excel</code> — install locally and hook into this controller.</p>

@endsection
