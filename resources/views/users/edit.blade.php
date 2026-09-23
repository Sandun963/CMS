@extends('layouts.app')
@section('title', 'Edit User')
@section('content')

<h4 class="mb-4">Edit User</h4>

<div class="card stat-card p-4" style="max-width: 640px;">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">

            <strong>Please correct the following:</strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>
    @endif


    <form
        method="POST"
        action="{{ route('users.update', $user) }}"
    >

        @csrf
        @method('PUT')


        <div class="row g-2">


            {{-- Full Name --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Full Name *
                </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        pattern="[A-Za-z ]+"
                        title="Full Name can contain letters and spaces only."
                        oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '')"
                        required
                    >

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Username --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Username *
                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control @error('username') is-invalid @enderror"
                    value="{{ old('username', $user->username) }}"
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z0-9._-]+"
                    title="Username can contain letters, numbers, dots, underscores and hyphens only."
                    required
                >

                @error('username')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

                <small class="text-muted">
                    Letters, numbers, dots, underscores and hyphens only.
                </small>

            </div>


            {{-- Email --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Email *
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    maxlength="255"
                    required
                >

                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- New Password --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    New Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    minlength="8"
                    maxlength="100"
                    autocomplete="new-password"
                >

                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

                <small class="text-muted">
                    Leave blank to keep the current password.
                    New passwords require uppercase, lowercase,
                    number and special character.
                </small>

            </div>


            {{-- Role --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Role *
                </label>

                <select
                    name="role_id"
                    class="form-select @error('role_id') is-invalid @enderror"
                    required
                >

                    @foreach($roles as $r)

                        <option
                            value="{{ $r->id }}"
                            @selected(
                                old(
                                    'role_id',
                                    $user->role_id
                                ) == $r->id
                            )
                        >
                            {{ $r->name }}
                        </option>

                    @endforeach

                </select>

                @error('role_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Administrator Type --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Administrator Type
                </label>

                <select
                    name="admin_scope"
                    class="form-select @error('admin_scope') is-invalid @enderror"
                >

                    <option value="">
                        Not Applicable
                    </option>

                    <option
                        value="IT"
                        @selected(
                            old(
                                'admin_scope',
                                $user->admin_scope
                            ) === 'IT'
                        )
                    >
                        IT Administrator
                    </option>

                    <option
                        value="Health"
                        @selected(
                            old(
                                'admin_scope',
                                $user->admin_scope
                            ) === 'Health'
                        )
                    >
                        Health Administrator
                    </option>

                    <option
                        value="Agriculture"
                        @selected(
                            old(
                                'admin_scope',
                                $user->admin_scope
                            ) === 'Agriculture'
                        )
                    >
                        Agriculture Administrator
                    </option>

                    <option
                        value="Other"
                        @selected(
                            old(
                                'admin_scope',
                                $user->admin_scope
                            ) === 'Other'
                        )
                    >
                        Other Administrator
                    </option>

                </select>

                @error('admin_scope')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

                <small class="text-muted">
                    Select only when Role is Administrator.
                </small>

            </div>


            {{-- Floor --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Floor
                </label>

                <select
                    name="floor_id"
                    id="floor_id"
                    class="form-select @error('floor_id') is-invalid @enderror"
                >

                    <option value="">
                        Select Floor
                    </option>

                    @foreach($floors as $floor)

                        <option
                            value="{{ $floor->id }}"
                            @selected(
                                old(
                                    'floor_id',
                                    $user->floor_id
                                ) == $floor->id
                            )
                        >
                            {{ $floor->name }}
                        </option>

                    @endforeach

                </select>

                @error('floor_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Division --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Division
                </label>

                <select
                    name="division_id"
                    id="division_id"
                    class="form-select @error('division_id') is-invalid @enderror"
                >

                    <option value="">
                        Select Division
                    </option>

                    @foreach($divisions as $division)

                        <option
                            value="{{ $division->id }}"
                            @selected(
                                old(
                                    'division_id',
                                    $user->division_id
                                ) == $division->id
                            )
                        >
                            {{ $division->name }}
                        </option>

                    @endforeach

                </select>

                @error('division_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Phone --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    id="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}"
                    inputmode="numeric"
                    maxlength="10"
                    pattern="[0-9]{1,10}"
                    title="Phone number must contain digits only and can contain up to 10 digits."
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                >

                @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror


            </div>


            {{-- Specialty --}}
            <div class="col-md-6 mb-3">

                <label class="form-label small fw-semibold">
                    Specialty
                </label>

                <input
                    type="text"
                    name="specialty"
                    class="form-control @error('specialty') is-invalid @enderror"
                    value="{{ old('specialty', $user->specialty) }}"
                    maxlength="100"
                    placeholder="e.g. Network, Hardware"
                >

                @error('specialty')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Active --}}
            <div class="col-12 mb-3 form-check">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="form-check-input"
                    id="is_active"
                    @checked(
                        old(
                            'is_active',
                            $user->is_active
                        )
                    )
                >

                <label
                    class="form-check-label"
                    for="is_active"
                >
                    Active
                </label>

            </div>

        </div>


        <button
            type="submit"
            class="btn btn-primary"
        >

            <i class="bi bi-check2 me-1"></i>

            Save Changes

        </button>


        <a
            href="{{ route('users.index') }}"
            class="btn btn-outline-secondary"
        >
            Cancel
        </a>

    </form>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const floorSelect =
        document.getElementById('floor_id');

    const divisionSelect =
        document.getElementById('division_id');

    const currentDivisionId =
        @json(old('division_id', $user->division_id));


    /*
    |--------------------------------------------------------------------------
    | Load Divisions
    |--------------------------------------------------------------------------
    */

    function loadDivisions(floorId, selectedDivisionId = null) {

        divisionSelect.innerHTML =
            '<option value="">Select Division</option>';


        if (!floorId) {

            divisionSelect.disabled = true;

            return;

        }


        divisionSelect.disabled = true;

        divisionSelect.innerHTML =
            '<option value="">Loading divisions...</option>';


        fetch(`/locations/floors/${floorId}/divisions`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {

            if (!response.ok) {
                throw new Error('Unable to load divisions.');
            }

            return response.json();

        })
        .then(divisions => {

            divisionSelect.innerHTML =
                '<option value="">Select Division</option>';


            divisions.forEach(division => {

                const option =
                    document.createElement('option');

                option.value =
                    division.id;

                option.textContent =
                    division.name;


                if (
                    selectedDivisionId &&
                    String(selectedDivisionId) === String(division.id)
                ) {

                    option.selected = true;

                }


                divisionSelect.appendChild(option);

            });


            divisionSelect.disabled = false;

        })
        .catch(error => {

            console.error(
                'Error loading divisions:',
                error
            );

            divisionSelect.innerHTML =
                '<option value="">Unable to load divisions</option>';

            divisionSelect.disabled = true;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Floor Changed
    |--------------------------------------------------------------------------
    */

    floorSelect.addEventListener('change', function () {

        loadDivisions(this.value);

    });


    /*
    |--------------------------------------------------------------------------
    | Load Current Division
    |--------------------------------------------------------------------------
    */

    if (floorSelect.value) {

        loadDivisions(
            floorSelect.value,
            currentDivisionId
        );

    } else {

        divisionSelect.disabled = true;

    }

});

</script>

@endsection