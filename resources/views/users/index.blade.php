@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manage Users</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add User</a>
</div>

<form method="GET" class="card stat-card p-3 mb-3">
    <div class="row g-2">
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                <option value="{{ $r->code }}" @selected(request('role') === $r->code)>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<div class="card stat-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Department</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $u->role->name }}</span></td>
                    <td>{{ $u->department->name ?? '-' }}</td>
                    <td>
                        @if($u->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif
                    </td>
                    <td>
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary mb-2">Edit</a>
                        @if($u->is_active)
                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Deactivate this user?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Deactivate</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links() }}</div>
</div>

@endsection
