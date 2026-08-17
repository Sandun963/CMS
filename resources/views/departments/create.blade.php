@extends('layouts.app')
@section('title', 'Add Department')
@section('content')
<h4 class="mb-4">Add Department</h4>
<div class="card stat-card p-4" style="max-width: 600px;">
    <form method="POST" action="{{ route('departments.store') }}">
        @csrf
        <div class="mb-3"><label class="form-label small fw-semibold">Ministry Name</label><input type="text" name="ministry_name" class="form-control" value="{{ old('ministry_name') }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Department Name *</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Code *</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Floor</label><input type="text" name="floor" class="form-control" value="{{ old('floor') }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Location</label><input type="text" name="location" class="form-control" value="{{ old('location') }}"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
        <button class="btn btn-primary">Create</button>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
