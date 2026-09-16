@extends('layouts.app')

@section('title', 'Reports')

@section('content')

{{-- =========================================================
     PAGE HEADER
========================================================= --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="mb-1">
            Reports
        </h4>

        <div class="text-muted small">
            Filter breakdown requests, preview results, and generate PDF reports.
        </div>

    </div>


    <div class="d-flex gap-2">

        <a
            href="{{ route('reports.export.pdf') }}"
            class="btn btn-danger"
        >
            <i class="bi bi-file-earmark-pdf me-1"></i>
            Export All PDF
        </a>

    </div>

</div>


{{-- =========================================================
     REPORT FILTERS
========================================================= --}}
<div class="card stat-card mb-4">

    <div class="card-header bg-white">

        <div class="d-flex align-items-center">

            <i class="bi bi-funnel me-2 text-danger"></i>

            <div>

                <strong>
                    Generate Filtered PDF Report
                </strong>

                <div class="text-muted small">
                    Select one or more filters to preview and generate a customized report.
                </div>

            </div>

        </div>

    </div>


    <div class="card-body">

        <form
            method="GET"
            action="{{ route('reports.index') }}"
            id="pdfReportFilterForm"
        >

            <div class="row g-3">


                {{-- =====================================================
                     REQUEST NUMBER
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="requestNumber"
                        class="form-label"
                    >
                        Request Number
                    </label>

                    <input
                        type="text"
                        name="request_number"
                        id="requestNumber"
                        class="form-control"
                        value="{{ request('request_number') }}"
                        placeholder="e.g. CMS/26/0001"
                    >

                </div>


                {{-- =====================================================
                     STATUS
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportStatus"
                        class="form-label"
                    >
                        Status
                    </label>

                    <select
                        name="status"
                        id="reportStatus"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        @foreach([
                            'New',
                            'Assigned',
                            'In Progress',
                            'Pending Reassignment',
                            'Resolved',
                            'Closed',
                            'Reopened'
                        ] as $status)

                            <option
                                value="{{ $status }}"
                                @selected(request('status') === $status)
                            >
                                {{ $status }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     FLOOR
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportFloor"
                        class="form-label"
                    >
                        Floor
                    </label>

                    <select
                        name="floor_id"
                        id="reportFloor"
                        class="form-select"
                    >

                        <option value="">
                            All Floors
                        </option>

                        @foreach($floors as $floor)

                            <option
                                value="{{ $floor->id }}"
                                @selected(
                                    (string) request('floor_id')
                                    ===
                                    (string) $floor->id
                                )
                            >
                                {{ $floor->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     DIVISION
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportDivision"
                        class="form-label"
                    >
                        Division
                    </label>

                    <select
                        name="division_id"
                        id="reportDivision"
                        class="form-select"
                        disabled
                    >

                        <option value="">
                            Select Floor First
                        </option>

                    </select>

                    <div class="form-text">
                        Select a floor to show its divisions.
                    </div>

                </div>


                {{-- =====================================================
                     AREA
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportArea"
                        class="form-label"
                    >
                        Area
                    </label>

                    <select
                        name="area_id"
                        id="reportArea"
                        class="form-select"
                        disabled
                    >

                        <option value="">
                            Select Division First
                        </option>

                    </select>

                </div>

                {{-- =====================================================
                    DEPARTMENT / MINISTRY USER
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportDepartmentUser"
                        class="form-label"
                    >
                        Department User
                    </label>

                    <select
                        name="department_user_id"
                        id="reportDepartmentUser"
                        class="form-select"
                    >

                        <option value="">
                            All Department Users
                        </option>

                        @foreach($departmentUsers as $departmentUser)

                            <option
                                value="{{ $departmentUser->id }}"
                                @selected(
                                    (string) request('department_user_id')
                                    ===
                                    (string) $departmentUser->id
                                )
                            >
                                {{ $departmentUser->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     TECHNICAL OFFICER
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportTechnician"
                        class="form-label"
                    >
                        Technical Officer
                    </label>

                    <select
                        name="technician_id"
                        id="reportTechnician"
                        class="form-select"
                    >

                        <option value="">
                            All Technical Officers
                        </option>

                        @foreach($technicians as $technician)

                            <option
                                value="{{ $technician->id }}"
                                @selected(
                                    (string) request('technician_id')
                                    ===
                                    (string) $technician->id
                                )
                            >
                                {{ $technician->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     FROM DATE
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="dateFrom"
                        class="form-label"
                    >
                        From Date
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        id="dateFrom"
                        class="form-control"
                        value="{{ request('date_from') }}"
                    >

                </div>


                {{-- =====================================================
                     TO DATE
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="dateTo"
                        class="form-label"
                    >
                        To Date
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        id="dateTo"
                        class="form-control"
                        value="{{ request('date_to') }}"
                    >

                </div>

            </div>


            {{-- =========================================================
                 ACTION BUTTONS
            ========================================================= --}}
            <div class="d-flex flex-wrap gap-2 mt-4">

                {{-- View Results --}}
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bi bi-search me-1"></i>
                    View Filtered Results
                </button>


                {{-- Download Filtered PDF --}}
                <button
                    type="submit"
                    class="btn btn-danger"
                    formaction="{{ route('reports.export.pdf') }}"
                >
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    Download Filtered PDF
                </button>


                {{-- Clear Filters --}}
                <a
                    href="{{ route('reports.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Clear Filters
                </a>

            </div>

        </form>

    </div>

</div>



{{-- =========================================================
     FILTERED REQUEST PREVIEW
========================================================= --}}

@if($hasFilters)

<div class="card stat-card mb-4">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <strong>
                    Filtered Breakdown Requests
                </strong>

                <div class="text-muted small">
                    Review these requests before downloading the PDF.
                </div>

            </div>


            <span class="badge bg-primary">

                {{ $filteredRequests->total() }}
                {{ $filteredRequests->total() === 1 ? 'Result' : 'Results' }}

            </span>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">

                    <tr>

                        <th>
                            Request No.
                        </th>

                        <th>
                            Problem
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Technical Officer
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($filteredRequests as $breakdownRequest)

                        <tr>


                            {{-- Request Number --}}
                            <td class="fw-semibold">

                                {{ $breakdownRequest->request_number }}

                            </td>


                            {{-- Problem --}}
                            <td>

                                {{ $breakdownRequest->title }}

                            </td>


                            {{-- Status --}}
                            <td>

                                <span
                                    class="badge {{ $breakdownRequest->statusBadgeClass() }}"
                                >
                                    {{ $breakdownRequest->status }}
                                </span>

                            </td>


                            {{-- Technical Officer --}}
                            <td>

                                {{
                                    $breakdownRequest->assignedTo?->name
                                    ?? 'Unassigned'
                                }}

                            </td>


                            {{-- Date --}}
                            <td>

                                {{
                                    $breakdownRequest
                                        ->created_at
                                        ->format('d/m/Y')
                                }}

                            </td>


                            {{-- View --}}
                            <td>

                                <a
                                    href="{{ route(
                                        'requests.show',
                                        $breakdownRequest
                                    ) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    View
                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >

                                <i class="bi bi-search fs-3 d-block mb-2"></i>

                                No breakdown requests match the selected filters.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- =====================================================
         PAGINATION
    ====================================================== --}}

    @if($filteredRequests->hasPages())

        <div class="card-footer bg-white">

            {{ $filteredRequests->links() }}

        </div>

    @endif

</div>

@endif



{{-- =========================================================
     FLOOR -> DIVISION -> AREA DEPENDENT DROPDOWNS
========================================================= --}}

<script>

function toTitleCase(text)
{
    if (!text) {
        return '';
    }

    return text
        .toLowerCase()
        .replace(
            /\b\w/g,
            function (char) {
                return char.toUpperCase();
            }
        );
}


document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const floorSelect =
            document.getElementById('reportFloor');

        const divisionSelect =
            document.getElementById('reportDivision');

        const areaSelect =
            document.getElementById('reportArea');


        /*
        |--------------------------------------------------------------------------
        | Existing Selected Values
        |--------------------------------------------------------------------------
        |
        | These values allow Division and Area to remain selected after
        | clicking "View Filtered Results".
        |
        */

        const selectedFloorId =
            @json(request('floor_id'));

        const selectedDivisionId =
            @json(request('division_id'));

        const selectedAreaId =
            @json(request('area_id'));


        /*
        |--------------------------------------------------------------------------
        | Load Divisions
        |--------------------------------------------------------------------------
        */

        async function loadDivisions(
            floorId,
            divisionToSelect = null,
            areaToSelect = null
        ) {

            /*
            | Reset Area
            */

            areaSelect.disabled = true;

            areaSelect.innerHTML =
                '<option value="">Select Division First</option>';


            /*
            | No Floor
            */

            if (!floorId) {

                divisionSelect.disabled = true;

                divisionSelect.innerHTML =
                    '<option value="">Select Floor First</option>';

                return;
            }


            /*
            | Loading
            */

            divisionSelect.disabled = true;

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


                /*
                | Default option
                */

                divisionSelect.innerHTML =
                    '<option value="">All Divisions</option>';


                /*
                | Add Divisions
                */

                divisions.forEach(
                    function (division) {

                        const option =
                            document.createElement('option');

                        option.value =
                            division.id;

                        option.textContent =
                            toTitleCase(
                                division.name
                            );


                        /*
                        | Restore selected Division
                        */

                        if (
                            divisionToSelect &&
                            String(division.id) ===
                            String(divisionToSelect)
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
                | Restore Area after Division
                */

                if (divisionToSelect) {

                    await loadAreas(
                        divisionToSelect,
                        areaToSelect
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
            areaToSelect = null
        ) {

            /*
            | No Division
            */

            if (!divisionId) {

                areaSelect.disabled = true;

                areaSelect.innerHTML =
                    '<option value="">Select Division First</option>';

                return;
            }


            /*
            | Loading
            */

            areaSelect.disabled = true;

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


                /*
                | Default option
                */

                areaSelect.innerHTML =
                    '<option value="">All Areas</option>';


                /*
                | Add Areas
                */

                areas.forEach(
                    function (area) {

                        const option =
                            document.createElement('option');


                        option.value =
                            area.id;


                        option.textContent =
                            toTitleCase(
                                area.name
                            );


                        /*
                        | Restore selected Area
                        */

                        if (
                            areaToSelect &&
                            String(area.id) ===
                            String(areaToSelect)
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
        | Restore Existing Filters
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Floor = 2
        | Division = Education Ministry
        | Area = Office Area
        |
        | User clicks View Filtered Results.
        |
        | After the page reloads all three selections remain visible.
        |
        */

        if (selectedFloorId) {

            loadDivisions(
                selectedFloorId,
                selectedDivisionId,
                selectedAreaId
            );

        }

    }
);

</script>

@endsection