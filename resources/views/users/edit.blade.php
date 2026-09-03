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

                <label class="form-label small fw-semibold">
                    Floor
                </label>

                <select name="floor_id"
                        id="floor_id"
                        class="form-select">

                    <option value="">Select Floor</option>

                    @foreach($floors as $floor)

                        <option value="{{ $floor->id }}"
                            @selected(
                                old('floor_id', $user->floor_id)
                                == $floor->id
                            )>

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

                    @foreach($divisions as $division)

                        <option value="{{ $division->id }}"
                            @selected(
                                old('division_id', $user->division_id)
                                == $division->id
                            )>

                            {{ $division->name }}

                        </option>

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
