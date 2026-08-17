@extends('layouts.app')
@section('title', 'Edit Department')
@section('content')
<h4 class="mb-4">Edit Department</h4>
<div class="card stat-card p-4" style="max-width: 600px;">
    <form method="POST" action="{{ route('departments.update', $department) }}">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label small fw-semibold">Ministry Name</label><input type="text" name="ministry_name" class="form-control" value="{{ old('ministry_name', $department->ministry_name) }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Department Name *</label><input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Code *</label><input type="text" name="code" class="form-control" value="{{ old('code', $department->code) }}" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Floor</label><input type="text" name="floor" class="form-control" value="{{ old('floor', $department->floor) }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Location</label><input type="text" name="location" class="form-control" value="{{ old('location', $department->location) }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $department->phone) }}"></div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $department->is_active))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button class="btn btn-primary">Save Changes</button>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
