@extends('layouts.app')
@section('title', 'Add User')
@section('content')

<h4 class="mb-4">Add User</h4>

<div class="card stat-card p-4" style="max-width: 640px;">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Full Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Username *</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Password *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Role *</label>
                <select name="role_id" class="form-select" required>
                    <option value="">Select role</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->id }}" @selected(old('role_id') == $r->id)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Department (Ministry Users)</label>
                <select name="department_id" class="form-select">
                    <option value="">N/A</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">Specialty (Technical Officers)</label>
                <input type="text" name="specialty" class="form-control" value="{{ old('specialty') }}" placeholder="e.g. Network, Hardware">
            </div>
        </div>
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Create User</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

@endsection
