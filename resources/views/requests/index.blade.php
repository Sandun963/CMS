@extends('layouts.app')
@section('title', auth()->user()->isMinistryUser() ? 'My Requests' : 'All Requests')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ auth()->user()->isMinistryUser() ? 'My Requests' : 'All Requests' }}</h4>
    @if(auth()->user()->isMinistryUser())
    <a href="{{ route('requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Submit New Request</a>
    @endif
</div>

<form method="GET" class="card stat-card p-3 mb-3">
    <div class="row g-2">
        <div class="col-md-3">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search request no. / title" value="{{ request('q') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach(['New','Assigned','In Progress','Resolved','Closed','Reopened'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        @if(!auth()->user()->isMinistryUser())
        <div class="col-md-3">
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected((string)request('department_id') === (string)$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
        </div>
    </div>
</form>

<div class="card stat-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Request No.</th><th>Department</th><th>Title</th><th>Status</th><th>Assigned To</th><th>Date</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr>
                    <td>{{ $r->request_number }}</td>
                    <td>{{ $r->department->name ?? '-' }}</td>
                    <td>{{ $r->title }}</td>
                    <td><span class="badge {{ $r->statusBadgeClass() }}">{{ $r->status }}</span></td>
                    <td>{{ $r->assignedTo->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $r->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('requests.show', $r) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $requests->links() }}</div>
</div>

@endsection
