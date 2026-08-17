@extends('layouts.app')
@section('title', 'Submit Request')
@section('content')

<h4 class="mb-4">Submit New Breakdown Request</h4>

<div class="card stat-card p-4" style="max-width: 720px;">
    <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select category (optional)</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Printer not working" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue in detail" required>{{ old('description') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Room 204, 3rd Floor">
            </div>
            <div class="col-md-6 mb-3">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Attach Images (optional)</label>
            <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
            <div class="form-text">JPG, PNG or PDF, max 5MB each.</div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Request</button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>

@endsection
