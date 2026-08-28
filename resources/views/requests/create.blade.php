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

            <label
                for="subCategory"
                class="form-label fw-semibold"
            >
                Sub Category
                <span class="text-danger">*</span>
            </label>

            <select
                name="title"
                id="subCategory"
                class="form-select"
                required
            >

                <option value="">
                    Select Sub Category
                </option>

                @foreach($subCategories as $subCategory)

                    <option
                        value="{{ $subCategory }}"
                        @selected(old('title') === $subCategory)
                    >
                        {{ $subCategory }}
                    </option>

                @endforeach

            </select>

        </div>

        <div class="mb-3">
            <label class="form-label">
                Description <span class="text-muted">(optional)</span>
            </label>

            <textarea name="description"
                    class="form-control"
                    rows="4"
                    placeholder="Describe the issue in detail (optional)">{{ old('description') }}</textarea>
        </div>

        <div class="row">

        {{-- Floor / Division --}}
        <div class="row">

            {{-- Floor --}}
            <div class="col-md-6 mb-3">

                <label
                    for="floorSelect"
                    class="form-label fw-semibold"
                >
                    Floor
                    <span class="text-danger">*</span>
                </label>

                <select
                    name="floor"
                    id="floorSelect"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select Floor
                    </option>

                    @foreach($floors as $floor)

                        <option
                            value="{{ $floor }}"
                            @selected(old('floor') === $floor)
                        >
                            {{ $floor }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Division --}}
            <div class="col-md-6 mb-3">

                <label
                    for="divisionSelect"
                    class="form-label fw-semibold"
                >
                    Division
                    <span class="text-danger">*</span>
                </label>

                <select
                    name="department_id"
                    id="divisionSelect"
                    class="form-select"
                    required
                    disabled
                >

                    <option value="">
                        Select Floor First
                    </option>

                </select>

            </div>

        </div>

        {{-- Area --}}
        <div class="mb-3">

            <label
                for="area"
                class="form-label fw-semibold"
            >
                Area
                <span class="text-danger">*</span>
            </label>

            <select
                name="area"
                id="area"
                class="form-select"
                required
            >

                <option value="">
                    Select Area
                </option>

                @foreach($areas as $area)

                    <option
                        value="{{ $area }}"
                        @selected(old('area') === $area)
                    >
                        {{ $area }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Machine Owner --}}
        <div class="row">

            <div class="col-md-6 mb-3">

                <label
                    for="machineOwnerName"
                    class="form-label fw-semibold"
                >
                    Machine Owner Name
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="machine_owner_name"
                    id="machineOwnerName"
                    class="form-control"
                    value="{{ old('machine_owner_name') }}"
                    placeholder="Enter machine owner's name"
                    required
                >

            </div>


            <div class="col-md-6 mb-3">

                <label
                    for="machineOwnerContact"
                    class="form-label fw-semibold"
                >
                    Contact Number
                    <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="machine_owner_contact"
                    id="machineOwnerContact"
                    class="form-control"
                    value="{{ old('machine_owner_contact') }}"
                    placeholder="e.g. 0712345678"
                    required
                >

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

    const floorSelect =
        document.getElementById('floorSelect');

    const divisionSelect =
        document.getElementById('divisionSelect');

    const divisions = @json($divisions);

    const oldDivisionId =
        "{{ old('department_id') }}";


    function loadDivisions() {

        const selectedFloor =
            floorSelect.value;


        /*
         * Reset dropdown
         */
        divisionSelect.innerHTML =
            '<option value="">Select Division</option>';


        /*
         * No floor selected
         */
        if (! selectedFloor) {

            divisionSelect.disabled = true;

            divisionSelect.innerHTML =
                '<option value="">Select Floor First</option>';

            return;
        }


        /*
         * Filter divisions by selected floor
         */
        const filteredDivisions =
            divisions.filter(function (division) {

                return division.floor === selectedFloor;

            });


        /*
         * Add matching divisions
         */
        filteredDivisions.forEach(function (division) {

            const option =
                document.createElement('option');

            option.value =
                division.id;

            option.textContent =
                division.name;


            if (
                String(division.id)
                ===
                String(oldDivisionId)
            ) {

                option.selected = true;

            }


            divisionSelect.appendChild(option);

        });


        divisionSelect.disabled = false;

    }


    /*
     * Floor changed
     */
    floorSelect.addEventListener(
        'change',
        loadDivisions
    );


    /*
     * Restore old selection
     * after validation error
     */
    if (floorSelect.value) {

        loadDivisions();

    }

});
</script>
@endsection
