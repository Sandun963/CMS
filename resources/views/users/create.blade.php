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
                <label class="form-label small fw-semibold">
                    Administrator Type
                </label>

                <select
                    name="admin_scope"
                    class="form-select"
                >
                    <option value="">
                        Not Applicable
                    </option>

                    <option value="IT"
                        @selected(old('admin_scope') === 'IT')>
                        IT Administrator
                    </option>

                    <option value="Health"
                        @selected(old('admin_scope') === 'Health')>
                        Health Administrator
                    </option>

                    <option value="Agriculture"
                        @selected(old('admin_scope') === 'Agriculture')>
                        Agriculture Administrator
                    </option>

                    <option value="Other"
                        @selected(old('admin_scope') === 'Other')>
                        Other Administrator
                    </option>
                </select>

                <small class="text-muted">
                    Select only when Role is Administrator.
                </small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">
                    Floor
                </label>

                <select name="floor_id"
                        id="floor_id"
                        class="form-select">

                    <option value="">Select Floor</option>

                    @foreach($floors as $floor)
                        <option value="{{ $floor->id }}"
                            @selected(old('floor_id') == $floor->id)>
                            {{ $floor->name }}
                        </option>
                    @endforeach

                </select>
            </div>


            <div class="col-md-6 mb-3">
                <label class="form-label small fw-semibold">
                    Division
                </label>

                <select name="division_id"
                        id="division_id"
                        class="form-select">

                    <option value="">Select Division</option>

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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const floorSelect = document.getElementById('floor_id');
    const divisionSelect = document.getElementById('division_id');

    floorSelect.addEventListener('change', function () {

        const floorId = this.value;

        divisionSelect.innerHTML =
            '<option value="">Select Division</option>';

        if (!floorId) {
            return;
        }

        fetch(`/locations/floors/${floorId}/divisions`)
            .then(response => response.json())
            .then(divisions => {

                divisions.forEach(division => {

                    const option = document.createElement('option');

                    option.value = division.id;
                    option.textContent = division.name;

                    divisionSelect.appendChild(option);
                });

            })
            .catch(error => {
                console.error('Error loading divisions:', error);
            });

    });

});
</script>
@endsection
