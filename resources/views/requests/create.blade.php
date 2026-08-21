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

            {{-- Ministry --}}
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">
                    Ministry <span class="text-danger">*</span>
                </label>

                <select
                    name="ministry_name"
                    id="ministrySelect"
                    class="form-select"
                    required
                >
                    <option value="">Select Ministry</option>

                    @foreach($ministries as $ministry)
                        <option
                            value="{{ $ministry }}"
                            @selected(old('ministry_name') === $ministry)
                        >
                            {{ $ministry }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Department --}}
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">
                    Department <span class="text-danger">*</span>
                </label>

                <select
                    name="department_id"
                    id="departmentSelect"
                    class="form-select"
                    required
                    disabled
                >
                    <option value="">Select Ministry First</option>
                </select>
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ministrySelect = document.getElementById('ministrySelect');
    const departmentSelect = document.getElementById('departmentSelect');

    const departments = @json($departments);

    const oldDepartmentId = "{{ old('department_id') }}";

    function loadDepartments() {

        const selectedMinistry = ministrySelect.value;

        departmentSelect.innerHTML =
            '<option value="">Select Department</option>';

        if (!selectedMinistry) {
            departmentSelect.disabled = true;
            departmentSelect.innerHTML =
                '<option value="">Select Ministry First</option>';

            return;
        }

        const filteredDepartments = departments.filter(function (department) {
            return department.ministry_name === selectedMinistry;
        });

        filteredDepartments.forEach(function (department) {

            const option = document.createElement('option');

            option.value = department.id;
            option.textContent = department.name;

            if (String(department.id) === String(oldDepartmentId)) {
                option.selected = true;
            }

            departmentSelect.appendChild(option);
        });

        departmentSelect.disabled = false;
    }

    ministrySelect.addEventListener('change', loadDepartments);

    if (ministrySelect.value) {
        loadDepartments();
    }

});
</script>
@endsection
