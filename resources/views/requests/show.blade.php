@extends('layouts.app')

@section('title', $breakdownRequest->request_number)

@section('content')

@php
    $user = auth()->user();

    $latestAssignment =
        $breakdownRequest->assignments->last();

    $latestOfficerAssignment =
        $latestAssignment?->officerAssignments->last();

    $latestWorkReport =
        $latestOfficerAssignment?->workReport;
@endphp


{{-- =========================================================
     PAGE HEADER
========================================================= --}}
<div class="d-flex justify-content-between align-items-start mb-3">

    <div>

        <h4 class="mb-1">

            {{ $breakdownRequest->request_number }}

            <span class="badge {{ $breakdownRequest->statusBadgeClass() }}">
                {{ $breakdownRequest->status }}
            </span>

        </h4>


        <div class="text-muted small">

            Submitted
            {{ $breakdownRequest->created_at->format('d M Y, H:i') }}

            by

            {{ $breakdownRequest->requestedBy->name }}

            ({{
                $breakdownRequest->division?->name
                ?? $breakdownRequest->department?->name
                ?? '-'
            }})

        </div>

    </div>


    <a
        href="{{ route('requests.index') }}"
        class="btn btn-outline-secondary btn-sm"
    >

        <i class="bi bi-arrow-left"></i>
        Back

    </a>

</div>


<div class="row g-3">


    {{-- =====================================================
         LEFT CONTENT
    ====================================================== --}}
    <div class="col-lg-8">


        {{-- =================================================
             REQUEST DETAILS
        ================================================== --}}
        <div class="card stat-card mb-3">

            <div class="card-header bg-white">

                <strong>
                    Request Details
                </strong>

            </div>


            <div class="card-body">


                {{-- Sub Category --}}
                <h6 class="mb-2">

                    {{ $breakdownRequest->title }}

                </h6>


                {{-- Description --}}
                <p class="mb-3">

                    {{ $breakdownRequest->description }}

                </p>


                {{-- =========================================
                     CATEGORY / FLOOR / DIVISION
                ========================================== --}}
                <div class="row small text-muted g-3">


                    {{-- Category --}}
                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Category:
                        </strong>

                        <span>
                            {{ $breakdownRequest->category->name ?? '-' }}
                        </span>

                    </div>


                    {{-- Floor --}}
                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Floor:
                        </strong>

                        <span>
                            {{
                                $breakdownRequest->floor?->name
                                ?? $breakdownRequest->department?->floor
                                ?? '-'
                            }}
                        </span>

                    </div>


                    {{-- Division --}}
                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Division:
                        </strong>

                        <span>
                            {{
                                $breakdownRequest->division?->name
                                ?? $breakdownRequest->department?->name
                                ?? '-'
                            }}
                        </span>

                    </div>

                </div>


                {{-- =========================================
                     AREA / MACHINE OWNER / CONTACT
                ========================================== --}}
                <div class="row small text-muted g-3 mt-1">


                    {{-- Area --}}
                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Area:
                        </strong>

                        <span>
                            {{
                                $breakdownRequest->areaLocation?->name
                                ?? $breakdownRequest->area
                                ?? '-'
                            }}
                        </span>

                    </div>


                    {{-- Machine Owner --}}
                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Machine Owner:
                        </strong>

                        <span>
                            {{ $breakdownRequest->machine_owner_name ?? '-' }}
                        </span>

                    </div>


                    <div class="col-md-4 mb-3">

                        <strong>Troubleshooter Name:</strong>

                        <div>
                            {{ $breakdownRequest->troubleshooter_name ?? '-' }}
                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <strong>Troubleshooter Contact:</strong>

                        <div>
                            {{ $breakdownRequest->troubleshooter_contact ?? '-' }}
                        </div>

                    </div>

                </div>


                {{-- =========================================
                     ATTACHMENTS
                ========================================== --}}
                @if($breakdownRequest->attachments->count())

                    <hr>

                    <div class="small fw-semibold mb-2">

                        Attachments

                    </div>


                    @foreach($breakdownRequest->attachments as $att)

                        <a
                            href="{{ $att->url() }}"
                            target="_blank"
                            class="badge bg-light text-dark border me-1 mb-1"
                        >

                            <i class="bi bi-paperclip"></i>

                            {{ $att->original_name }}

                        </a>

                    @endforeach

                @endif


            </div>

        </div>



        {{-- =================================================
             STEP 2
             ASSIGN OFFICER DIRECTLY ASSIGNS TECHNICIAN
        ================================================== --}}
            @if(
                $user->isAssignOfficer()
                &&
                in_array(
                    $breakdownRequest->status,
                    [
                        'New',
                        'Reopened',
                        'Pending Reassignment'
                    ]
                )
            )

            <div
                class="card stat-card mb-3
                       border-start border-4 border-primary"
            >

                <div class="card-header bg-white">

                    <strong>
                        Step 2 — Assign Technical Officer
                    </strong>

                </div>


                <div class="card-body">


                    <form
                        method="POST"
                        action="{{ route(
                            'officer-assignments.store',
                            $breakdownRequest
                        ) }}"
                    >

                        @csrf


                        <div class="row g-2">


                            {{-- Technical Officer --}}
                            <div class="col-md-5">

                                <label class="form-label small">

                                    Technical Officer

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <select
                                    name="technical_officer_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">

                                        Select technician

                                    </option>


                                    @foreach(
                                        $technicalOfficers
                                        as $t
                                    )

                                        <option
                                            value="{{ $t->id }}"
                                        >

                                            {{ $t->name }}

                                            @if($t->specialty)

                                                ({{ $t->specialty }})

                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- Due Date --}}
                            <div class="col-md-3">

                                <label class="form-label small">

                                    Due Date
                                    (optional)

                                </label>


                                <input
                                    type="date"
                                    name="due_date"
                                    class="form-control"
                                >

                            </div>


                            {{-- Note --}}
                            <div class="col-md-4">

                                <label class="form-label small">

                                    Note
                                    (optional)

                                </label>


                                <input
                                    type="text"
                                    name="note"
                                    class="form-control"
                                >

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary btn-sm mt-3"
                        >

                            <i class="bi bi-send me-1"></i>

                            Assign Technical Officer

                        </button>


                    </form>

                </div>

            </div>

        @endif



        {{-- =================================================
             STEP 3
             TECHNICAL OFFICER STARTS / COMPLETES WORK
        ================================================== --}}
        @if(
            $user->isTechnicalOfficer()
            &&
            $latestOfficerAssignment
            &&
            $latestOfficerAssignment->technical_officer_id
                === $user->id
            &&
            $latestOfficerAssignment->status !== 'Done'
        )


            {{-- =============================================
                 START WORK BUTTON
            ============================================== --}}
            @if(
                $latestOfficerAssignment->status === 'Pending'
            )

                <div
                    class="card stat-card mb-3
                           border-start border-4 border-success"
                >

                    <div class="card-header bg-white">

                        <strong>
                            Step 3 — Start Assigned Job
                        </strong>

                    </div>


                    <div class="card-body">

                        <p class="text-muted mb-3">

                            This breakdown has been assigned to you.
                            Click the button below when you start
                            working on it.

                        </p>


                        <form
                            method="POST"
                            action="{{ route(
                                'work-reports.start',
                                $latestOfficerAssignment
                            ) }}"
                        >

                            @csrf


                            <button
                                type="submit"
                                class="btn btn-success"
                            >

                                <i
                                    class="bi bi-play-fill me-1"
                                ></i>

                                Start Work

                            </button>

                        </form>

                    </div>

                </div>

            @endif



            {{-- =============================================
                 WORK REPORT FORM
            ============================================== --}}
            @if(
                $latestOfficerAssignment->status
                    === 'In Progress'
            )

                <div
                    class="card stat-card mb-3
                           border-start border-4 border-info"
                >

                    <div class="card-header bg-white">

                        <strong>

                            Step 3 —
                            Resolve Breakdown /
                            File Work Report

                        </strong>

                    </div>


                    <div class="card-body">


                        <form
                            method="POST"
                            action="{{ route(
                                'work-reports.store',
                                $latestOfficerAssignment
                            ) }}"
                            enctype="multipart/form-data"
                        >

                            @csrf


                            {{-- Problem Identified --}}
                            <div class="mb-2">

                                <label class="form-label small">

                                    Problem Identified

                                </label>


                                <textarea
                                    name="problem_identified"
                                    class="form-control"
                                    rows="2"
                                ></textarea>

                            </div>


                            {{-- Work Performed --}}
                            <div class="mb-2">

                                <label class="form-label small">

                                    Work Performed

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <textarea
                                    name="work_performed"
                                    class="form-control"
                                    rows="2"
                                    required
                                ></textarea>

                            </div>


                            {{-- Parts Used --}}
                            <div class="mb-2">

                                <label class="form-label small">

                                    Parts Used
                                    (optional)

                                </label>


                                <input
                                    type="text"
                                    name="parts_used"
                                    class="form-control"
                                >

                            </div>


                            <div class="row g-2">


                                {{-- Completion Status --}}
                                <div class="col-md-6">

                                    <label class="form-label small">

                                        Completion Status

                                        <span class="text-danger">
                                            *
                                        </span>

                                    </label>


                                    <select
                                        name="completion_status"
                                        class="form-select"
                                        required
                                    >

                                        <option value="Done">
                                            Done
                                        </option>

                                        <option value="Not Done">
                                            Not Done
                                        </option>

                                    </select>

                                </div>


                                {{-- Photos --}}
                                <div class="col-md-6">

                                    <label class="form-label small">

                                        Attach Photos
                                        (optional)

                                    </label>


                                    <input
                                        type="file"
                                        name="photos[]"
                                        class="form-control"
                                        multiple
                                        accept=".jpg,.jpeg,.png"
                                    >

                                </div>

                            </div>


                            {{-- Remarks --}}
                            <div class="mb-2 mt-2">

                                <label class="form-label small">

                                    Remarks / Notes

                                </label>


                                <textarea
                                    name="remarks"
                                    class="form-control"
                                    rows="2"
                                ></textarea>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-info btn-sm mt-2"
                            >

                                <i
                                    class="bi bi-tools me-1"
                                ></i>

                                Submit Work Report

                            </button>


                        </form>

                    </div>

                </div>

            @endif

        @endif



        {{-- =================================================
             STEP 4
             MINISTRY USER VERIFIES AND CLOSES
        ================================================== --}}
        @if(
            $user->isMinistryUser()
            &&
            $breakdownRequest->requested_by === $user->id
            &&
            $breakdownRequest->status === 'Resolved'
            &&
            $latestWorkReport
            &&
            !$latestWorkReport->confirmation
        )

            <div
                class="card stat-card mb-3
                       border-start border-4 border-primary"
            >

                <div class="card-header bg-white">

                    <strong>
                        Step 4 — Verify & Close
                    </strong>

                </div>


                <div class="card-body">


                    <p class="small text-muted">

                        The technician reported this breakdown
                        as resolved. Please confirm whether
                        the issue has been resolved.

                    </p>


                    <form
                        method="POST"
                        action="{{ route(
                            'confirmations.store',
                            $latestWorkReport
                        ) }}"
                    >

                        @csrf


                        {{-- Confirmation --}}
                        <div class="mb-2">

                            <label class="form-label small">

                                Is the issue resolved?

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <select
                                name="is_resolved"
                                class="form-select"
                                required
                            >

                                <option value="Yes">

                                    Yes — Close Request

                                </option>


                                <option value="No">

                                    No — Reopen Request

                                </option>

                            </select>

                        </div>


                        {{-- Feedback --}}
                        <div class="mb-2">

                            <label class="form-label small">

                                Feedback
                                (optional)

                            </label>


                            <textarea
                                name="feedback"
                                class="form-control"
                                rows="2"
                            ></textarea>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                        >

                            <i
                                class="bi bi-check2-circle me-1"
                            ></i>

                            Submit Feedback

                        </button>


                    </form>

                </div>

            </div>

        @endif



        {{-- =================================================
             WORK REPORT HISTORY
        ================================================== --}}
        @foreach(
            $breakdownRequest->assignments
            as $assignment
        )

            @foreach(
                $assignment->officerAssignments
                as $oa
            )

                @if($oa->workReport)

                    <div class="card stat-card mb-3">


                        <div class="card-header bg-white">

                            <strong>
                                Work Report
                            </strong>

                            —

                            {{ $oa->technicalOfficer->name }}

                        </div>


                        <div class="card-body small">


                            <p>

                                <strong>
                                    Problem:
                                </strong>

                                {{
                                    $oa->workReport
                                        ->problem_identified
                                    ?: '-'
                                }}

                            </p>


                            <p>

                                <strong>
                                    Work performed:
                                </strong>

                                {{
                                    $oa->workReport
                                        ->work_performed
                                }}

                            </p>


                            <p>

                                <strong>
                                    Parts used:
                                </strong>

                                {{
                                    $oa->workReport
                                        ->parts_used
                                    ?: '-'
                                }}

                            </p>


                            <p>

                                <strong>
                                    Status:
                                </strong>


                                <span
                                    class="badge {{
                                        $oa->workReport
                                            ->completion_status
                                            === 'Done'
                                        ? 'bg-success'
                                        : 'bg-warning text-dark'
                                    }}"
                                >

                                    {{
                                        $oa->workReport
                                            ->completion_status
                                    }}

                                </span>

                            </p>



                            {{-- Work Report Attachments --}}
                            @if(
                                $oa->workReport
                                    ->attachments
                                    ->count()
                            )

                                <div class="mb-1">

                                    @foreach(
                                        $oa->workReport
                                            ->attachments
                                        as $att
                                    )

                                        <a
                                            href="{{ $att->url() }}"
                                            target="_blank"
                                            class="
                                                badge
                                                bg-light
                                                text-dark
                                                border
                                                me-1
                                            "
                                        >

                                            <i
                                                class="bi bi-image"
                                            ></i>

                                            {{
                                                $att->original_name
                                            }}

                                        </a>

                                    @endforeach

                                </div>

                            @endif



                            {{-- Department Confirmation --}}
                            @if(
                                $oa->workReport
                                    ->confirmation
                            )

                                <hr>


                                <p class="mb-0">

                                    <strong>
                                        Department confirmation:
                                    </strong>


                                    <span
                                        class="badge {{
                                            $oa->workReport
                                                ->confirmation
                                                ->is_resolved
                                                === 'Yes'
                                            ? 'bg-success'
                                            : 'bg-danger'
                                        }}"
                                    >

                                        {{
                                            $oa->workReport
                                                ->confirmation
                                                ->is_resolved
                                                === 'Yes'
                                            ? 'Confirmed Resolved'
                                            : 'Not Resolved / Reopened'
                                        }}

                                    </span>

                                </p>


                                @if(
                                    $oa->workReport
                                        ->confirmation
                                        ->feedback
                                )

                                    <p class="mb-0 text-muted">

                                        "{{
                                            $oa->workReport
                                                ->confirmation
                                                ->feedback
                                        }}"

                                    </p>

                                @endif

                            @endif


                        </div>

                    </div>

                @endif

            @endforeach

        @endforeach


    </div>



    {{-- =====================================================
         RIGHT SIDEBAR
    ====================================================== --}}
    <div class="col-lg-4">


        {{-- =================================================
             ASSIGNMENT CHAIN
        ================================================== --}}
        <div class="card stat-card mb-3">

            <div class="card-header bg-white">

                <strong>
                    Assignment Chain
                </strong>

            </div>


            <ul class="list-group list-group-flush small">


                {{-- Requested By --}}
                <li class="list-group-item">

                    <div class="text-muted">
                        Requested by
                    </div>

                    {{
                        $breakdownRequest
                            ->requestedBy
                            ->name
                    }}

                </li>


                {{-- Assign Officer --}}
                @if($latestAssignment)

                    <li class="list-group-item">

                        <div class="text-muted">

                            Managed / Assigned by
                            Assign Officer

                        </div>


                        <strong>

                            {{
                                $latestAssignment
                                    ->assignOfficer
                                    ->name
                                ??
                                $latestAssignment
                                    ->assignedBy
                                    ->name
                                ??
                                '-'
                            }}

                        </strong>

                    </li>

                @endif


                {{-- Technician --}}
                @if($latestOfficerAssignment)

                    <li class="list-group-item">

                        <div class="text-muted">

                            Assigned to Technical Officer

                        </div>


                        <strong>

                            {{
                                $latestOfficerAssignment
                                    ->technicalOfficer
                                    ->name
                            }}

                        </strong>


                        @if(
                            $latestOfficerAssignment
                                ->due_date
                        )

                            <div class="text-muted">

                                Due:

                                {{
                                    $latestOfficerAssignment
                                        ->due_date
                                        ->format('d/m/Y')
                                }}

                            </div>

                        @endif

                    </li>

                @endif


            </ul>

        </div>



        {{-- =================================================
             ACTIVITY LOG
        ================================================== --}}
        <div class="card stat-card">

            <div class="card-header bg-white">

                <strong>
                    Activity Log
                </strong>

            </div>


            <ul class="list-group list-group-flush small">


                @forelse(
                    $breakdownRequest->activityLogs
                    as $log
                )

                    <li class="list-group-item">


                        <div>

                            {{ $log->action }}

                        </div>


                        @if($log->details)

                            <div class="text-muted">

                                {{ $log->details }}

                            </div>

                        @endif


                        <div
                            class="text-muted"
                            style="font-size: 0.75rem;"
                        >

                            {{
                                $log->user->name
                                ?? 'System'
                            }}

                            ·

                            {{
                                $log->created_at
                                    ->diffForHumans()
                            }}

                        </div>


                    </li>


                @empty


                    <li
                        class="
                            list-group-item
                            text-muted
                        "
                    >

                        No activity yet.

                    </li>


                @endforelse


            </ul>

        </div>


    </div>


</div>

@endsection