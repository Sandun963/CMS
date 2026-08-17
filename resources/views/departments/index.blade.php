@extends('layouts.app')
@section('title', 'Manage Departments')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manage Departments</h4>
    <a href="{{ route('departments.create') }}" class="btn btn-primary"><i class="bi bi-building-add me-1"></i>Add Department</a>
</div>

<div class="card stat-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Name</th><th>Code</th><th>Floor</th><th>Location</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($departments as $d)
                <tr>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->code }}</td>
                    <td>{{ $d->floor ?? '-' }}</td>
                    <td>{{ $d->location ?? '-' }}</td>
                    <td>@if($d->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif</td>
                    <td>
                        <a href="{{ route('departments.edit', $d) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @if($d->is_active)
                        <form method="POST" action="{{ route('departments.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Deactivate this department?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Deactivate</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No departments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $departments->links() }}</div>
</div>

@endsection
