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

        {{-- Export all requests as PDF --}}
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
                <strong>Generate Filtered PDF Report</strong>

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
                        placeholder="e.g. BRK-2026-0001"
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
                        name="floor"
                        id="reportFloor"
                        class="form-select"
                    >

                        <option value="">
                            All Floors
                        </option>

                        @foreach($floors as $floor)

                            <option value="{{ $floor }}">
                                {{ $floor }}
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
                        name="department_id"
                        id="reportDivision"
                        class="form-select"
                    >

                        <option value="">
                            All Divisions
                        </option>

                        @foreach($divisions as $division)

                            <option
                                value="{{ $division->id }}"
                                data-floor="{{ $division->floor }}"
                            >
                                {{ $division->name }}
                            </option>

                        @endforeach

                    </select>

                    <div class="form-text">
                        Select a floor to show only its divisions.
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
                        name="area"
                        id="reportArea"
                        class="form-select"
                    >

                        <option value="">
                            All Areas
                        </option>

                        @foreach($areas as $area)

                            <option value="{{ $area }}">
                                {{ $area }}
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

                            <option value="{{ $technician->id }}">
                                {{ $technician->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     DATE FROM
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
                    >

                </div>


                {{-- =====================================================
                     DATE TO
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
                    >

                </div>

            </div>


            {{-- =====================================================
                 FILTER ACTION BUTTONS
            ====================================================== --}}
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


    {{-- =====================================================
         REQUESTS BY DEPARTMENT
    ====================================================== --}}
    <div class="col-md-6">

        <div class="card stat-card p-3 h-100">

            <strong class="mb-2 d-block">
                Requests by Department
            </strong>

            <div class="table-responsive">

                <table class="table table-sm mb-0">

                    <tbody>

                        @forelse($byDepartment as $row)

                            <tr>

                                <td>
                                    {{ $row->department->name ?? 'Unknown' }}
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


    {{-- =====================================================
         REQUESTS BY STATUS
    ====================================================== --}}
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


    {{-- =====================================================
         REQUESTS BY CATEGORY
    ====================================================== --}}
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
                                    {{ $row->category->name ?? 'Uncategorized' }}
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


    {{-- =====================================================
         REQUESTS BY TECHNICAL OFFICER
    ====================================================== --}}
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
                                    {{ $row->assignedTo->name ?? 'Unassigned' }}
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
     MINISTRY -> DEPARTMENT DEPENDENT DROPDOWN
========================================================= --}}
<script>

        document.addEventListener('DOMContentLoaded', function () {

            const floorSelect =
                document.getElementById('reportFloor');

            const divisionSelect =
                document.getElementById('reportDivision');

            const clearButton =
                document.getElementById('clearReportFilters');

            /*
            * Save all divisions.
            */
            const allDivisions = Array.from(
                divisionSelect.querySelectorAll(
                    'option[data-floor]'
                )
            ).map(function (option) {

                return {
                    value: option.value,
                    text: option.textContent.trim(),
                    floor: option.dataset.floor
                };

            });

            /*
            * Filter divisions using selected floor.
            */
            function updateDivisions() {

                const selectedFloor =
                    floorSelect.value;

                divisionSelect.innerHTML =
                    '<option value="">All Divisions</option>';

                allDivisions.forEach(function (division) {

                    if (
                        selectedFloor === ''
                        ||
                        division.floor === selectedFloor
                    ) {

                        const option =
                            document.createElement('option');

                        option.value =
                            division.value;

                        option.textContent =
                            division.text;

                        option.dataset.floor =
                            division.floor;

                        divisionSelect.appendChild(
                            option
                        );

                    }

                });

            }

            floorSelect.addEventListener(
                'change',
                updateDivisions
            );

            clearButton.addEventListener(
                'click',
                function () {

                    setTimeout(function () {

                        updateDivisions();

                    }, 0);

                }
            );

        });

</script>

@endsection