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

        {{-- Export all requests as Excel --}}
        <a
            href="{{ route('reports.export.excel') }}"
            class="btn btn-success"
        >
            <i class="bi bi-file-earmark-excel me-1"></i>
            Export Excel
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
                     MINISTRY
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportMinistry"
                        class="form-label"
                    >
                        Ministry
                    </label>

                    <select
                        name="ministry_name"
                        id="reportMinistry"
                        class="form-select"
                    >

                        <option value="">
                            All Ministries
                        </option>

                        @foreach($ministries as $ministry)

                            <option value="{{ $ministry }}">
                                {{ $ministry }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     DEPARTMENT
                ====================================================== --}}
                <div class="col-md-4">

                    <label
                        for="reportDepartment"
                        class="form-label"
                    >
                        Department
                    </label>

                    <select
                        name="department_id"
                        id="reportDepartment"
                        class="form-select"
                    >

                        <option value="">
                            All Departments
                        </option>

                        @foreach($departments as $department)

                            <option
                                value="{{ $department->id }}"
                                data-ministry="{{ $department->ministry_name }}"
                            >
                                {{ $department->name }}
                            </option>

                        @endforeach

                    </select>

                    <div class="form-text">
                        Select a ministry to show only its departments.
                    </div>

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

    const ministrySelect =
        document.getElementById('reportMinistry');

    const departmentSelect =
        document.getElementById('reportDepartment');

    const clearButton =
        document.getElementById('clearReportFilters');


    /*
     * Store all department options when the page loads.
     */
    const allDepartmentOptions = Array.from(
        departmentSelect.querySelectorAll(
            'option[data-ministry]'
        )
    ).map(function (option) {

        return {
            value: option.value,
            text: option.textContent.trim(),
            ministry: option.dataset.ministry
        };

    });


    /*
     * Rebuild Department dropdown.
     */
    function updateDepartments() {

        const selectedMinistry =
            ministrySelect.value;


        /*
         * Remove current options.
         */
        departmentSelect.innerHTML =
            '<option value="">All Departments</option>';


        /*
         * Add matching departments.
         */
        allDepartmentOptions.forEach(function (department) {

            if (
                selectedMinistry === ''
                ||
                department.ministry === selectedMinistry
            ) {

                const option =
                    document.createElement('option');

                option.value =
                    department.value;

                option.textContent =
                    department.text;

                option.dataset.ministry =
                    department.ministry;

                departmentSelect.appendChild(option);

            }

        });

    }


    /*
     * When Ministry changes,
     * update Department list.
     */
    ministrySelect.addEventListener(
        'change',
        updateDepartments
    );


    /*
     * Reset button.
     */
    clearButton.addEventListener(
        'click',
        function () {

            /*
             * Wait until the browser resets
             * the form fields.
             */
            setTimeout(function () {

                updateDepartments();

            }, 0);

        }
    );

});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ministrySelect =
        document.getElementById('reportMinistry');

    const departmentSelect =
        document.getElementById('reportDepartment');

    const allDepartmentOptions =
        Array.from(
            departmentSelect.querySelectorAll('option[data-ministry]')
        );

    ministrySelect.addEventListener('change', function () {

        const selectedMinistry = this.value;

        departmentSelect.innerHTML =
            '<option value="">All Departments</option>';

        allDepartmentOptions.forEach(function (option) {

            if (
                selectedMinistry === ''
                ||
                option.dataset.ministry === selectedMinistry
            ) {

                departmentSelect.appendChild(
                    option.cloneNode(true)
                );

            }

        });

    });

});
</script>

@endsection