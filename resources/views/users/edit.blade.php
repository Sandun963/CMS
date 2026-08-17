@extends('layouts.app')
@section('title', 'Edit User')
@section('content')

<h4 class="mb-4">Edit User</h4>

<div class="card stat-card p-4" style="max-width: 640px;">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Full Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Username *</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Role *</label>
                <select name="role_id" class="form-select" required>
                    @foreach($roles as $r)
                    <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">N/A</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Specialty</label>
                <input type="text" name="specialty" class="form-control" value="{{ old('specialty', $user->specialty) }}">
            </div>
            <div class="col-12 mb-3 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $user->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

@endsection
