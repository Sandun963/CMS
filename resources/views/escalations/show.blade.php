@extends('layouts.app')

@section('title', 'Escalated Case')

@section('content')

@php

    $requestData = $escalation->breakdownRequest;

    $latestAssignment =
        $requestData->assignments->last();

    $latestOfficerAssignment =
        $latestAssignment?->officerAssignments->last();

@endphp


{{-- =========================================================
     PAGE HEADER
========================================================= --}}

<div class="d-flex justify-content-between align-items-start mb-3">

    <div>

        <h4 class="mb-1">

            {{ $requestData->request_number }}

            <span class="badge {{ $requestData->statusBadgeClass() }}">
                {{ $requestData->status }}
            </span>

        </h4>


        <div class="text-muted small">

            Submitted
            {{ $requestData->created_at->format('d M Y, H:i') }}

            by

            {{ $requestData->requestedBy?->name ?? '-' }}

            ({{ $requestData->division?->name ?? '-' }})

        </div>

    </div>


    <a
        href="{{ route('escalations.index') }}"
        class="btn btn-outline-secondary btn-sm"
    >

        <i class="bi bi-arrow-left"></i>
        Back

    </a>

</div>



<div class="row g-3">


    {{-- =====================================================
         LEFT SIDE
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


                {{-- Problem --}}
                <h6 class="mb-2">
                    {{ $requestData->title }}
                </h6>


                {{-- Description --}}
                <p class="mb-4">
                    {{ $requestData->description ?: '-' }}
                </p>



                {{-- Row 1 --}}
                <div class="row g-3 mb-3">


                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Category:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->category?->name ?? '-' }}

                        </span>

                    </div>



                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Floor:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->floor?->name ?? '-' }}

                        </span>

                    </div>



                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Division:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->division?->name ?? '-' }}

                        </span>

                    </div>

                </div>



                {{-- Row 2 --}}
                <div class="row g-3 mb-3">


                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Area:
                        </strong>

                        <span class="text-muted">

                            {{
                                $requestData->areaLocation?->name
                                ?? $requestData->area
                                ?? '-'
                            }}

                        </span>

                    </div>



                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Machine Owner:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->machine_owner_name ?? '-' }}

                        </span>

                    </div>



                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Troubleshooter Name:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->troubleshooter_name ?? '-' }}

                        </span>

                    </div>

                </div>



                {{-- Row 3 --}}
                <div class="row g-3">


                    <div class="col-md-4">

                        <strong class="d-block mb-1">
                            Troubleshooter Contact:
                        </strong>

                        <span class="text-muted">

                            {{ $requestData->troubleshooter_contact ?? '-' }}

                        </span>

                    </div>

                </div>



                {{-- Request Attachments --}}
                @if($requestData->attachments->count())

                    <hr>


                    <div class="fw-semibold small mb-2">
                        Request Attachments
                    </div>


                    @foreach(
                        $requestData->attachments as $attachment
                    )

                        <a
                            href="{{ $attachment->url() }}"
                            target="_blank"
                            class="badge bg-light text-dark border me-1 mb-1"
                        >

                            <i class="bi bi-paperclip me-1"></i>

                            {{ $attachment->original_name }}

                        </a>

                    @endforeach

                @endif


            </div>

        </div>



        {{-- =================================================
             ALL TECHNICIAN WORK REPORTS
        ================================================== --}}

        @php
            $reportNumber = 0;
        @endphp


        @foreach(
            $requestData->assignments as $assignment
        )

            @foreach(
                $assignment->officerAssignments as $oa
            )

                @if($oa->workReport)

                    @php
                        $reportNumber++;
                        $report = $oa->workReport;
                    @endphp


                    <div class="card stat-card mb-3">


                        <div class="card-header bg-white">

                            <strong>
                                Work Report {{ $reportNumber }}
                            </strong>

                            —

                            {{ $oa->technicalOfficer?->name ?? '-' }}

                        </div>


                        <div class="card-body">


                            {{-- Problem --}}
                            <p>

                                <strong>
                                    Problem:
                                </strong>

                                {{ $report->problem_identified ?: '-' }}

                            </p>



                            {{-- Work Performed --}}
                            <p>

                                <strong>
                                    Work performed:
                                </strong>

                                {{ $report->work_performed ?: '-' }}

                            </p>



                            {{-- Parts --}}
                            <p>

                                <strong>
                                    Parts used:
                                </strong>

                                {{ $report->parts_used ?: '-' }}

                            </p>



                            {{-- Remarks --}}
                            <p>

                                <strong>
                                    Remarks:
                                </strong>

                                {{ $report->remarks ?: '-' }}

                            </p>



                            {{-- Status --}}
                            <p>

                                <strong>
                                    Status:
                                </strong>


                                <span
                                    class="badge
                                    {{
                                        $report->completion_status === 'Done'
                                        ? 'bg-success'
                                        : 'bg-warning text-dark'
                                    }}"
                                >

                                    {{ $report->completion_status }}

                                </span>

                            </p>



                            {{-- Dates --}}
                            <div class="row g-3 mb-3">


                                <div class="col-md-6">

                                    <strong>
                                        Attended At:
                                    </strong>

                                    <div class="text-muted">

                                        {{
                                            $report->attended_at
                                            ? $report->attended_at->format(
                                                'd M Y, h:i A'
                                            )
                                            : '-'
                                        }}

                                    </div>

                                </div>



                                <div class="col-md-6">

                                    <strong>
                                        Reported At:
                                    </strong>

                                    <div class="text-muted">

                                        {{
                                            $report->reported_at
                                            ? $report->reported_at->format(
                                                'd M Y, h:i A'
                                            )
                                            : '-'
                                        }}

                                    </div>

                                </div>

                            </div>



                            {{-- Work Report Attachments --}}
                            @if($report->attachments->count())

                                <hr>


                                <div class="fw-semibold small mb-2">

                                    Technician Attachments

                                </div>


                                <div class="row g-2">


                                    @foreach(
                                        $report->attachments as $attachment
                                    )

                                        <div class="col-md-4">


                                            @if(
                                                str_starts_with(
                                                    $attachment->mime_type ?? '',
                                                    'image/'
                                                )
                                            )

                                                <a
                                                    href="{{ $attachment->url() }}"
                                                    target="_blank"
                                                    class="text-decoration-none"
                                                >

                                                    <img
                                                        src="{{ $attachment->url() }}"
                                                        alt="{{ $attachment->original_name }}"
                                                        class="img-fluid rounded border"
                                                        style="
                                                            width: 100%;
                                                            height: 150px;
                                                            object-fit: cover;
                                                        "
                                                    >

                                                    <div
                                                        class="small text-muted mt-1 text-truncate"
                                                    >

                                                        {{ $attachment->original_name }}

                                                    </div>

                                                </a>


                                            @else

                                                <a
                                                    href="{{ $attachment->url() }}"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-primary"
                                                >

                                                    <i class="bi bi-paperclip"></i>

                                                    {{ $attachment->original_name }}

                                                </a>

                                            @endif


                                        </div>

                                    @endforeach

                                </div>

                            @endif



                            {{-- Department Confirmation --}}
                            @if($report->confirmation)

                                <hr>


                                <strong>
                                    Department Confirmation:
                                </strong>


                                <span
                                    class="badge
                                    {{
                                        $report->confirmation->is_resolved
                                        === 'Yes'
                                        ? 'bg-success'
                                        : 'bg-danger'
                                    }}"
                                >

                                    {{
                                        $report->confirmation->is_resolved
                                        === 'Yes'
                                        ? 'Confirmed Resolved'
                                        : 'Not Resolved'
                                    }}

                                </span>


                                @if(
                                    $report->confirmation->feedback
                                )

                                    <div class="text-muted mt-2">

                                        Feedback:

                                        {{
                                            $report
                                                ->confirmation
                                                ->feedback
                                        }}

                                    </div>

                                @endif

                            @endif


                        </div>

                    </div>

                @endif

            @endforeach

        @endforeach



        @if($reportNumber === 0)

            <div class="alert alert-light border">

                No technician work reports found.

            </div>

        @endif



        {{-- =================================================
             ESCALATION INFORMATION
        ================================================== --}}

        <div class="card stat-card mb-3">

            <div class="card-header bg-white">

                <strong>
                    Escalation Information
                </strong>

            </div>


            <div class="card-body">


                <div class="row g-3">


                    <div class="col-md-6">

                        <strong>
                            Forwarded By:
                        </strong>

                        <div class="text-muted">

                            {{ $escalation->forwardedBy?->name ?? '-' }}

                        </div>

                    </div>



                    <div class="col-md-6">

                        <strong>
                            Forwarded At:
                        </strong>

                        <div class="text-muted">

                            {{
                                $escalation->forwarded_at
                                ? $escalation
                                    ->forwarded_at
                                    ->format(
                                        'd F Y - h:i A'
                                    )
                                : '-'
                            }}

                        </div>

                    </div>



                    <div class="col-12">

                        <strong>
                            Reason for Forwarding:
                        </strong>

                        <div
                            class="border rounded bg-light p-3 mt-1"
                            style="white-space: pre-wrap;"
                        >{{ $escalation->reason ?: '-' }}</div>

                    </div>

                </div>


            </div>

        </div>



        {{-- =================================================
             IT ADMIN DECISION
        ================================================== --}}

        @if($escalation->status === 'Pending Review')


            <div
                class="card stat-card mb-3
                       border-start border-4 border-warning"
            >

                <div class="card-header bg-white">

                    <strong>
                        IT Administrator Decision
                    </strong>

                </div>


                <div class="card-body">


                    <form
                        method="POST"
                        action="{{ route(
                            'escalations.decide',
                            $escalation
                        ) }}"
                    >

                        @csrf


                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Decision

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <select
                                name="admin_decision"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select decision
                                </option>


                                <option
                                    value="Outsource Recommended"
                                >
                                    Outsource Recommended
                                </option>


                                <option
                                    value="Closed - Unable to Resolve"
                                >
                                    Closed - Unable to Resolve
                                </option>

                            </select>

                        </div>



                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                IT Administrator Remarks

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <textarea
                                name="admin_remarks"
                                class="form-control"
                                rows="4"
                                required
                            >{{ old('admin_remarks') }}</textarea>

                        </div>



                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-circle me-1"></i>

                            Submit Decision

                        </button>


                    </form>


                </div>

            </div>


        @else


            <div class="card stat-card mb-3">

                <div class="card-header bg-white">

                    <strong>
                        IT Administrator Decision
                    </strong>

                </div>


                <div class="card-body">


                    <p>

                        <strong>
                            Decision:
                        </strong>

                        {{ $escalation->admin_decision }}

                    </p>


                    <p>

                        <strong>
                            Remarks:
                        </strong>

                        {{ $escalation->admin_remarks ?: '-' }}

                    </p>


                    <p>

                        <strong>
                            Reviewed By:
                        </strong>

                        {{ $escalation->reviewedBy?->name ?? '-' }}

                    </p>


                    <p class="mb-0">

                        <strong>
                            Reviewed At:
                        </strong>

                        {{
                            $escalation->reviewed_at
                            ? $escalation
                                ->reviewed_at
                                ->format(
                                    'd F Y - h:i A'
                                )
                            : '-'
                        }}

                    </p>


                </div>

            </div>


        @endif


    </div>



    {{-- =====================================================
         RIGHT SIDE
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

                    <strong>
                        {{ $requestData->requestedBy?->name ?? '-' }}
                    </strong>

                </li>



                {{-- =========================================
                     ALL ASSIGNMENTS
                ========================================== --}}

                @foreach(
                    $requestData->assignments as $assignment
                )


                    {{-- Assign Officer --}}
                    <li class="list-group-item">

                        <div class="text-muted">

                            Managed / Assigned by Assign Officer

                        </div>


                        <strong>

                            {{
                                $assignment->assignOfficer?->name
                                ?? $assignment->assignedBy?->name
                                ?? '-'
                            }}

                        </strong>


                        @if($assignment->assigned_at)

                            <div
                                class="text-muted"
                                style="font-size: 0.75rem;"
                            >

                                {{
                                    $assignment
                                        ->assigned_at
                                        ->format(
                                            'd M Y, h:i A'
                                        )
                                }}

                            </div>

                        @endif

                    </li>



                    {{-- Every Technician --}}
                    @foreach(
                        $assignment->officerAssignments
                        as $oa
                    )

                        <li class="list-group-item">


                            <div class="text-muted">

                                Assigned to Technical Officer

                            </div>


                            <strong>

                                {{
                                    $oa->technicalOfficer?->name
                                    ?? '-'
                                }}

                            </strong>


                            <div class="mt-1">


                                <span
                                    class="badge
                                    {{
                                        $oa->status === 'Done'
                                        ? 'bg-success'
                                        : (
                                            $oa->status === 'Not Done'
                                            ? 'bg-danger'
                                            : (
                                                $oa->status === 'In Progress'
                                                ? 'bg-info text-dark'
                                                : 'bg-warning text-dark'
                                            )
                                        )
                                    }}"
                                >

                                    {{ $oa->status }}

                                </span>

                            </div>


                            @if($oa->due_date)

                                <div
                                    class="text-muted mt-1"
                                >

                                    Due:

                                    {{
                                        $oa
                                            ->due_date
                                            ->format('d/m/Y')
                                    }}

                                </div>

                            @endif


                        </li>

                    @endforeach


                @endforeach



                {{-- Escalated --}}
                <li class="list-group-item">

                    <div class="text-muted">

                        Forwarded to IT Administrator

                    </div>


                    <strong>

                        IT Administrator Review

                    </strong>


                    @if($escalation->forwarded_at)

                        <div
                            class="text-muted"
                            style="font-size: 0.75rem;"
                        >

                            {{
                                $escalation
                                    ->forwarded_at
                                    ->format(
                                        'd M Y, h:i A'
                                    )
                            }}

                        </div>

                    @endif

                </li>


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
                    $requestData->activityLogs as $log
                )

                    <li class="list-group-item">


                        <div class="fw-medium">

                            {{ $log->action }}

                        </div>


                        @if($log->details)

                            <div class="text-muted mt-1">

                                {{ $log->details }}

                            </div>

                        @endif


                        <div
                            class="text-muted mt-1"
                            style="font-size: 0.75rem;"
                        >

                            {{
                                $log->user?->name
                                ?? 'System'
                            }}

                            ·

                            {{
                                $log
                                    ->created_at
                                    ->diffForHumans()
                            }}

                        </div>


                    </li>


                @empty


                    <li
                        class="list-group-item text-muted"
                    >

                        No activity recorded.

                    </li>


                @endforelse


            </ul>

        </div>


    </div>


</div>


@endsection