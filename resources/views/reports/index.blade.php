@extends('layouts.app')

@section('title', 'Reports')

@section('content')

{{-- =========================================================
     PAGE HEADER
========================================================= --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h4 class="mb-1">Reports</h4>

        <div class="text-muted small">
            View breakdown statistics and generate reports.
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
     PDF REPORT FILTERS
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
                    Select one or more filters to generate a customized report.
                </div>

            </div>

        </div>

    </div>


    <div class="card-body">

        <form
            method="GET"
            action="{{ route('reports.export.pdf') }}"
            id="pdfReportFilterForm"
        >

            <div class="row g-3">


                {{-- Request Number --}}
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
                        placeholder="e.g. BRK-2026-0001"
                    >

                </div>


                {{-- Status --}}
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
                            'Resolved',
                            'Closed',
                            'Reopened'
                        ] as $status)

                            <option value="{{ $status }}">
                                {{ $status }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Floor --}}
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

                            <option value="{{ $floor->id }}">
                                {{ $floor->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Division --}}
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


                {{-- Area --}}
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


                {{-- Technical Officer --}}
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

                            <option value="{{ $technician->id }}">
                                {{ $technician->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- From Date --}}
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
                    >

                </div>


                {{-- To Date --}}
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
                    >

                </div>

            </div>


            {{-- Filter Action Buttons --}}
            <div class="d-flex flex-wrap gap-2 mt-4">

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    Download Filtered PDF
                </button>


                <button
                    type="reset"
                    class="btn btn-outline-secondary"
                    id="clearReportFilters"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Clear Filters
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     REPORT STATISTICS
========================================================= --}}
<div class="row g-3">


    {{-- Requests by Division --}}
    <div class="col-md-6">

        <div class="card stat-card p-3 h-100">

            <strong class="mb-2 d-block">
                Requests by Division
            </strong>

            <div class="table-responsive">

                <table class="table table-sm mb-0">

                    <tbody>

                        @forelse($byDivision as $row)

                            <tr>

                                <td>
                                    {{ $row->division?->name ?? 'Unknown' }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ $row->total }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td class="text-muted">
                                    No data yet.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Requests by Status --}}
    <div class="col-md-6">

        <div class="card stat-card p-3 h-100">

            <strong class="mb-2 d-block">
                Requests by Status
            </strong>

            <div class="table-responsive">

                <table class="table table-sm mb-0">

                    <tbody>

                        @forelse($byStatus as $row)

                            <tr>

                                <td>
                                    {{ $row->status }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ $row->total }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td class="text-muted">
                                    No data yet.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Requests by Category --}}
    <div class="col-md-6">

        <div class="card stat-card p-3 h-100">

            <strong class="mb-2 d-block">
                Requests by Category
            </strong>

            <div class="table-responsive">

                <table class="table table-sm mb-0">

                    <tbody>

                        @forelse($byCategory as $row)

                            <tr>

                                <td>
                                    {{ $row->category?->name ?? 'Uncategorized' }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ $row->total }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td class="text-muted">
                                    No data yet.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Requests by Technical Officer --}}
    <div class="col-md-6">

        <div class="card stat-card p-3 h-100">

            <strong class="mb-2 d-block">
                Requests by Technical Officer
            </strong>

            <div class="table-responsive">

                <table class="table table-sm mb-0">

                    <tbody>

                        @forelse($byTechnician as $row)

                            <tr>

                                <td>
                                    {{ $row->assignedTo?->name ?? 'Unassigned' }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ $row->total }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td class="text-muted">
                                    No data yet.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     FLOOR -> DIVISION -> AREA DEPENDENT DROPDOWNS
========================================================= --}}
<script>

function toTitleCase(text) {
    if (!text) return '';

    return text
        .toLowerCase()
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}


document.addEventListener(
    'DOMContentLoaded',
    function () {

        const floorSelect =
            document.getElementById('reportFloor');

        const divisionSelect =
            document.getElementById('reportDivision');

        const areaSelect =
            document.getElementById('reportArea');

        const clearButton =
            document.getElementById('clearReportFilters');


        /*
        |--------------------------------------------------------------------------
        | Load Divisions
        |--------------------------------------------------------------------------
        */

        async function loadDivisions(floorId) {

            areaSelect.disabled = true;

            areaSelect.innerHTML =
                '<option value="">Select Division First</option>';


            if (!floorId) {

                divisionSelect.disabled = true;

                divisionSelect.innerHTML =
                    '<option value="">Select Floor First</option>';

                return;

            }


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


                divisionSelect.innerHTML =
                    '<option value="">All Divisions</option>';


                divisions.forEach(function (division) {

                    const option =
                        document.createElement('option');

                    option.value =
                        division.id;

                    option.textContent =
                        toTitleCase(division.name);

                    divisionSelect.appendChild(
                        option
                    );

                });


                divisionSelect.disabled = false;


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

        async function loadAreas(divisionId) {

            if (!divisionId) {

                areaSelect.disabled = true;

                areaSelect.innerHTML =
                    '<option value="">Select Division First</option>';

                return;

            }


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


                areaSelect.innerHTML =
                    '<option value="">All Areas</option>';


                areas.forEach(function (area) {

                    const option =
                        document.createElement('option');

                    option.value =
                        area.id;

                    option.textContent =
                        toTitleCase(area.name);

                    areaSelect.appendChild(
                        option
                    );

                });


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
        | Clear Filters
        |--------------------------------------------------------------------------
        */

        clearButton.addEventListener(
            'click',
            function () {

                setTimeout(
                    function () {

                        divisionSelect.disabled = true;

                        divisionSelect.innerHTML =
                            '<option value="">Select Floor First</option>';

                        areaSelect.disabled = true;

                        areaSelect.innerHTML =
                            '<option value="">Select Division First</option>';

                    },
                    0
                );

            }
        );

    }
);

</script>

@endsection
