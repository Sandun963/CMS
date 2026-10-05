@extends('layouts.app')

@section('title', 'Submit Request')

@section('content')

<h4 class="mb-4">Submit New Breakdown Request</h4>

<div class="card stat-card p-4" style="max-width: 720px;">

        <form
            id="breakdownRequestForm"
            method="POST"
            action="{{ route('requests.store') }}"
            enctype="multipart/form-data"
        >
        @csrf


        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


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


                @if(auth()->user()->isMinistryUser())

                    {{-- 
                        Ministry User:
                        Floor is automatically taken from the user's account.
                        Show it as locked/read-only.
                    --}}

                    <select
                        class="form-select"
                        disabled
                    >
                        <option selected>
                            {{ $floors->first()?->name ?? 'No Floor Assigned' }}
                        </option>
                    </select>

                    <input
                        type="hidden"
                        name="floor_id"
                        id="floorSelect"
                        value="{{ auth()->user()->floor_id }}"
                    >

                @else

                    <select
                        name="floor_id"
                        id="floorSelect"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Floor
                        </option>

                        @foreach($floors as $floor)

                            <option
                                value="{{ $floor->id }}"
                                @selected(old('floor_id') == $floor->id)
                            >
                                {{ $floor->name }}
                            </option>

                        @endforeach

                    </select>

                @endif

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
                    name="division_id"
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
                for="areaSelect"
                class="form-label fw-semibold"
            >
                Area
                <span class="text-danger">*</span>
            </label>

            <select
                name="area_id"
                id="areaSelect"
                class="form-select"
                required
                disabled
            >

                <option value="">
                    Select Division First
                </option>

            </select>

        </div>




        {{-- Category --}}
        <div class="mb-3">

            <label
                for="category"
                class="form-label fw-semibold"
            >
                Category <span class="text-danger">*</span>
            </label>

            <select
                name="category_id"
                id="category"
                class="form-select"
            >
                <option value="">
                    Select category 
                </option>

                @foreach($categories as $cat)

                    <option
                        value="{{ $cat->id }}"
                        @selected(old('category_id') == $cat->id)
                    >
                        {{ $cat->name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- Description --}}
        <div class="mb-3">

            <label
                for="description"
                class="form-label"
            >
                Description
                <span class="text-muted">
                    (optional)
                </span>
            </label>

            <textarea
                name="description"
                id="description"
                class="form-control"
                rows="4"
            >{{ old('description') }}</textarea>

        </div>


        {{-- Machine Owner --}}
        <div class="mb-3">

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
                pattern="[A-Za-z ]+"
                oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '').toUpperCase()"
                required
            >

        </div>


        {{-- Troubleshooter Details --}}
        <div class="row">

            {{-- Troubleshooter Name --}}
            <div class="col-md-6 mb-3">

                <label
                    for="troubleshooterName"
                    class="form-label fw-semibold"
                >
                    Troubleshooter Name

                    <span class="text-muted fw-normal">
                        (optional)
                    </span>
                </label>

                <input
                    type="text"
                    name="troubleshooter_name"
                    id="troubleshooterName"
                    class="form-control"
                    value="{{ old('troubleshooter_name') }}"
                    placeholder="Enter troubleshooter's name"
                    pattern="[A-Za-z ]*"
                    oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '').toUpperCase()"
                >

            </div>


            {{-- Troubleshooter Contact --}}
            <div class="col-md-6 mb-3">

                <label
                    for="troubleshooterContact"
                    class="form-label fw-semibold"
                >
                    Troubleshooter Contact Number

                    <span class="text-muted fw-normal">
                        (optional)
                    </span>
                </label>

                <input
                    type="text"
                    name="troubleshooter_contact"
                    id="troubleshooterContact"
                    class="form-control"
                    value="{{ old('troubleshooter_contact') }}"
                    inputmode="numeric"
                    maxlength="10"
                    pattern="[0-9]{1,10}"
                    title="Contact number can contain up to 10 digits"
                    oninput="
                        this.value = this.value
                            .replace(/[^0-9]/g, '')
                            .slice(0, 10);
                    "
                >

            </div>

        </div>


                {{-- Attachments --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Attach Images (optional)
                    </label>

                    <input
                        type="file"
                        name="attachments[]"
                        class="form-control"
                        multiple
                        accept=".jpg,.jpeg,.png,.pdf"
                    >

                    <div class="form-text">
                        JPG, PNG or PDF, max 5MB each.
                    </div>

                </div>


                {{-- Buttons --}}
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bi bi-send me-1"></i>
                    Submit Request
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="btn btn-outline-secondary"
                >
                    Cancel
                </a>

            </form>

        </div>

{{-- Submit Confirmation Modal --}}
<div
    class="modal fade"
    id="submitConfirmationModal"
    tabindex="-1"
    aria-labelledby="submitConfirmationModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5
                    class="modal-title"
                    id="submitConfirmationModalLabel"
                >
                    Confirm Request Submission
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body">
                Are you sure you want to submit this request?
            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="confirmSubmitRequest"
                >
                    <i class="bi bi-check-circle me-1"></i>
                    Yes, Submit Request
                </button>

            </div>

        </div>
    </div>
</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const floorSelect =
            document.getElementById('floorSelect');

        const divisionSelect =
            document.getElementById('divisionSelect');

        const areaSelect =
            document.getElementById('areaSelect');

        const requestForm =
            document.getElementById('breakdownRequestForm');

        const confirmSubmitButton =
            document.getElementById('confirmSubmitRequest');

        const confirmationModalElement =
            document.getElementById('submitConfirmationModal');

        const confirmationModal =
            new bootstrap.Modal(confirmationModalElement);

        let submissionConfirmed = false;


        /*
        |--------------------------------------------------------------------------
        | Request Submission Confirmation
        |--------------------------------------------------------------------------
        */

        requestForm.addEventListener(
            'submit',
            function (event) {

                if (submissionConfirmed) {
                    return;
                }

                event.preventDefault();

                /*
                | Check HTML required fields first.
                | Popup appears only when the form is valid.
                */
                if (!requestForm.checkValidity()) {

                    requestForm.reportValidity();
                    return;

                }

                confirmationModal.show();

            }
        );


        confirmSubmitButton.addEventListener(
            'click',
            function () {

                submissionConfirmed = true;

                /*
                | Prevent accidental double-click submission.
                */
                confirmSubmitButton.disabled = true;

                confirmSubmitButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...';

                confirmationModal.hide();

                requestForm.requestSubmit();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Old Values
        |--------------------------------------------------------------------------
        |
        | These values are used when validation fails and Laravel redirects
        | the user back to this form.
        |
        */

        const oldDivisionId =
            @json(old('division_id'));

        const oldAreaId =
            @json(old('area_id'));



        /*
        |--------------------------------------------------------------------------
        | Load Divisions
        |--------------------------------------------------------------------------
        */

        async function loadDivisions(
            floorId,
            selectedDivisionId = null
        ) {

            divisionSelect.disabled = true;

            areaSelect.disabled = true;


            /*
            | Reset Area
            */

            areaSelect.innerHTML =
                '<option value="">Select Division First</option>';


            /*
            | No Floor Selected
            */

            if (!floorId) {

                divisionSelect.innerHTML =
                    '<option value="">Select Floor First</option>';

                return;
            }


            /*
            | Loading Message
            */

            divisionSelect.innerHTML =
                '<option value="">Loading divisions...</option>';


            try {

                const response = await fetch(
                    `/locations/floors/${floorId}/divisions`,
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );


                if (!response.ok) {

                    throw new Error(
                        'Unable to load divisions.'
                    );

                }


                const divisions =
                    await response.json();


                divisionSelect.innerHTML =
                    '<option value="">Select Division</option>';


                /*
                | Add Divisions
                */

                divisions.forEach(
                    function (division) {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            division.id;

                        option.textContent =
                            division.name;


                        /*
                        | Restore previously selected division
                        */

                        if (
                            selectedDivisionId &&
                            String(division.id) ===
                            String(selectedDivisionId)
                        ) {

                            option.selected = true;

                        }


                        divisionSelect.appendChild(
                            option
                        );

                    }
                );


                divisionSelect.disabled = false;


                /*
                | If validation failed previously,
                | restore the selected Area too.
                */

                if (selectedDivisionId) {

                    await loadAreas(
                        selectedDivisionId,
                        oldAreaId
                    );

                }


            } catch (error) {

                console.error(
                    'Error loading divisions:',
                    error
                );


                divisionSelect.innerHTML =
                    '<option value="">Unable to load divisions</option>';

                divisionSelect.disabled = true;

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Load Areas
        |--------------------------------------------------------------------------
        */

        async function loadAreas(
            divisionId,
            selectedAreaId = null
        ) {

            areaSelect.disabled = true;


            /*
            | No Division Selected
            */

            if (!divisionId) {

                areaSelect.innerHTML =
                    '<option value="">Select Division First</option>';

                return;
            }


            /*
            | Loading Message
            */

            areaSelect.innerHTML =
                '<option value="">Loading areas...</option>';


            try {

                const response = await fetch(
                    `/locations/divisions/${divisionId}/areas`,
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );


                if (!response.ok) {

                    throw new Error(
                        'Unable to load areas.'
                    );

                }


                const areas =
                    await response.json();


                areaSelect.innerHTML =
                    '<option value="">Select Area</option>';


                /*
                | Add Areas
                */

                areas.forEach(
                    function (area) {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            area.id;

                        option.textContent =
                            area.name;


                        /*
                        | Restore previously selected Area
                        */

                        if (
                            selectedAreaId &&
                            String(area.id) ===
                            String(selectedAreaId)
                        ) {

                            option.selected = true;

                        }


                        areaSelect.appendChild(
                            option
                        );

                    }
                );


                areaSelect.disabled = false;


            } catch (error) {

                console.error(
                    'Error loading areas:',
                    error
                );


                areaSelect.innerHTML =
                    '<option value="">Unable to load areas</option>';

                areaSelect.disabled = true;

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Floor Changed
        |--------------------------------------------------------------------------
        */

        floorSelect.addEventListener(
            'change',
            function () {

                loadDivisions(
                    this.value
                );

            }
        );



        /*
        |--------------------------------------------------------------------------
        | Division Changed
        |--------------------------------------------------------------------------
        */

        divisionSelect.addEventListener(
            'change',
            function () {

                loadAreas(
                    this.value
                );

            }
        );



        /*
        |--------------------------------------------------------------------------
        | Restore Old Values
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | User submits form
        |         ↓
        | Validation fails
        |         ↓
        | Laravel returns back()
        |         ↓
        | Floor, Division and Area are restored
        |
        */

        if (floorSelect.value) {

            loadDivisions(
                floorSelect.value,
                oldDivisionId
            );

        }

    }
);

</script>

@endsection