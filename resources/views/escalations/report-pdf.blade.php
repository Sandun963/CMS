<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Complaint Report -
        {{ $escalation->breakdownRequest->request_number }}
    </title>

    <style>
        @page {
            margin: 25px 30px 35px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.45;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 17px;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 3px 0 0;
            font-size: 13px;
            font-weight: normal;
        }

        .header p {
            margin: 3px 0 0;
            font-size: 9px;
            color: #555;
        }

        .report-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .report-meta td {
            padding: 5px 7px;
            border: 1px solid #ccc;
        }

        .report-meta .label {
            width: 20%;
            font-weight: bold;
            background: #f2f2f2;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e9ecef;
            border-left: 4px solid #333;
            padding: 6px 8px;
            margin-top: 15px;
            margin-bottom: 7px;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.details th,
        table.details td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
            text-align: left;
        }

        table.details th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .label-cell {
            width: 20%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .description-box {
            border: 1px solid #ccc;
            padding: 8px;
            min-height: 35px;
            white-space: pre-wrap;
        }

        .report-box {
            border: 1px solid #bbb;
            padding: 8px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .report-box-title {
            font-weight: bold;
            font-size: 10px;
            padding-bottom: 5px;
            margin-bottom: 6px;
            border-bottom: 1px solid #ccc;
        }

        .decision-box {
            border: 2px solid #444;
            padding: 10px;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .decision-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .status {
            font-weight: bold;
        }

        .small {
            font-size: 9px;
        }

        .muted {
            color: #666;
        }

        .text-center {
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .footer-note {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #aaa;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>

@php

    $requestData = $escalation->breakdownRequest;

    $reportNumber = 0;

@endphp


{{-- ============================================================
     HEADER
============================================================ --}}

<div class="header">

    <h1>
        Complaint Report
    </h1>

    <h2>
        ICT Unit
    </h2>

    <p>
        System Generated Escalated Complaint Report
    </p>

</div>


{{-- ============================================================
     REPORT META
============================================================ --}}

<table class="report-meta">

    <tr>

        <td class="label">
            Request No.
        </td>

        <td>
            <strong>
                {{ $requestData->request_number ?? '-' }}
            </strong>
        </td>

        <td class="label">
            Current Status
        </td>

        <td>
            <strong>
                {{ $requestData->status ?? '-' }}
            </strong>
        </td>

    </tr>


    <tr>

        <td class="label">
            Submitted At
        </td>

        <td>
            {{
                $requestData->created_at
                ? $requestData->created_at->format(
                    'd F Y - h:i A'
                )
                : '-'
            }}
        </td>

        <td class="label">
            Report Generated
        </td>

        <td>
            {{ now()->format('d F Y - h:i A') }}
        </td>

    </tr>

</table>


{{-- ============================================================
     1. COMPLAINT INFORMATION
============================================================ --}}

<div class="section-title">
    1. Complaint Information
</div>


<table class="details">

    <tr>

        <td class="label-cell">
            Requested By
        </td>

        <td>
            {{ $requestData->requestedBy?->name ?? '-' }}
        </td>

        <td class="label-cell">
            Category
        </td>

        <td>
            {{ $requestData->category?->name ?? '-' }}
        </td>

    </tr>


    <tr>

        <td class="label-cell">
            Problem / Complaint
        </td>

        <td colspan="3">
            {{ $requestData->title ?? '-' }}
        </td>

    </tr>

</table>


<strong>
    Description:
</strong>

<div class="description-box">
    {{ $requestData->description ?: '-' }}
</div>


{{-- ============================================================
     2. LOCATION AND CONTACT INFORMATION
============================================================ --}}

<div class="section-title">
    2. Location and Contact Information
</div>


<table class="details">

    <tr>

        <td class="label-cell">
            Floor
        </td>

        <td>
            {{ $requestData->floor?->name ?? '-' }}
        </td>

        <td class="label-cell">
            Division
        </td>

        <td>
            {{ $requestData->division?->name ?? '-' }}
        </td>

    </tr>


    <tr>

        <td class="label-cell">
            Area
        </td>

        <td>
            {{
                $requestData->areaLocation?->name
                ?? $requestData->area
                ?? '-'
            }}
        </td>

        <td class="label-cell">
            Machine Owner
        </td>

        <td>
            {{ $requestData->machine_owner_name ?? '-' }}
        </td>

    </tr>


    <tr>

        <td class="label-cell">
            Troubleshooter
        </td>

        <td>
            {{ $requestData->troubleshooter_name ?? '-' }}
        </td>

        <td class="label-cell">
            Contact
        </td>

        <td>
            {{ $requestData->troubleshooter_contact ?? '-' }}
        </td>

    </tr>

</table>


{{-- ============================================================
     3. ORIGINAL REQUEST ATTACHMENTS
============================================================ --}}

<div class="section-title">
    3. Original Request Attachments
</div>


@if($requestData->attachments->count())

    <table class="details">

        <thead>

        <tr>

            <th style="width: 8%;">
                #
            </th>

            <th>
                File Name
            </th>

            <th style="width: 25%;">
                Type
            </th>

        </tr>

        </thead>

        <tbody>

        @foreach(
            $requestData->attachments as $index => $attachment
        )

            <tr>

                <td>
                    {{ $index + 1 }}
                </td>

                <td>
                    {{ $attachment->original_name ?? '-' }}
                </td>

                <td>
                    {{ $attachment->mime_type ?? '-' }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

@else

    <p class="muted">
        No original request attachments were submitted.
    </p>

@endif


{{-- ============================================================
     4. ASSIGNMENT / TECHNICAL HISTORY
============================================================ --}}

<div class="section-title">
    4. Assignment / Technical History
</div>


@forelse(
    $requestData->assignments as $assignmentIndex => $assignment
)

    <div class="report-box">

        <div class="report-box-title">

            Assignment
            {{ $assignmentIndex + 1 }}

        </div>


        <table class="details">

            <tr>

                <td class="label-cell">
                    Assign Officer
                </td>

                <td>

                    {{
                        $assignment->assignOfficer?->name
                        ?? $assignment->assignedBy?->name
                        ?? '-'
                    }}

                </td>


                <td class="label-cell">
                    Assigned At
                </td>

                <td>

                    {{
                        $assignment->assigned_at
                        ? $assignment
                            ->assigned_at
                            ->format(
                                'd F Y - h:i A'
                            )
                        : '-'
                    }}

                </td>

            </tr>

        </table>


        @forelse(
            $assignment->officerAssignments as $oaIndex => $oa
        )

            <table class="details">

                <tr>

                    <td class="label-cell">
                        Technical Officer
                    </td>

                    <td>
                        {{ $oa->technicalOfficer?->name ?? '-' }}
                    </td>

                    <td class="label-cell">
                        Assignment Status
                    </td>

                    <td>
                        <strong>
                            {{ $oa->status ?? '-' }}
                        </strong>
                    </td>

                </tr>


                <tr>

                    <td class="label-cell">
                        Due Date
                    </td>

                    <td>

                        {{
                            $oa->due_date
                            ? $oa->due_date->format('d F Y')
                            : '-'
                        }}

                    </td>

                    <td class="label-cell">
                        Assigned By
                    </td>

                    <td>
                        {{ $oa->assignedBy?->name ?? '-' }}
                    </td>

                </tr>

            </table>

        @empty

            <p class="muted">
                No Technical Officer assignments recorded.
            </p>

        @endforelse

    </div>

@empty

    <p class="muted">
        No assignment history recorded.
    </p>

@endforelse


{{-- ============================================================
     5. TECHNICAL OFFICER WORK REPORTS
============================================================ --}}

<div class="section-title">
    5. Technical Officer Work Reports
</div>


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


            <div class="report-box">

                <div class="report-box-title">

                    Work Report {{ $reportNumber }}

                    -

                    {{
                        $oa->technicalOfficer?->name
                        ?? '-'
                    }}

                </div>


                <table class="details">

                    <tr>

                        <td class="label-cell">
                            Technical Officer
                        </td>

                        <td>
                            {{ $oa->technicalOfficer?->name ?? '-' }}
                        </td>

                        <td class="label-cell">
                            Completion Status
                        </td>

                        <td class="status">
                            {{ $report->completion_status ?? '-' }}
                        </td>

                    </tr>


                    <tr>

                        <td class="label-cell">
                            Attended At
                        </td>

                        <td>

                            {{
                                $report->attended_at
                                ? $report
                                    ->attended_at
                                    ->format(
                                        'd F Y - h:i A'
                                    )
                                : '-'
                            }}

                        </td>

                        <td class="label-cell">
                            Reported At
                        </td>

                        <td>

                            {{
                                $report->reported_at
                                ? $report
                                    ->reported_at
                                    ->format(
                                        'd F Y - h:i A'
                                    )
                                : '-'
                            }}

                        </td>

                    </tr>

                </table>


                <table class="details">

                    <tr>

                        <td class="label-cell">
                            Problem Identified
                        </td>

                        <td>
                            {{ $report->problem_identified ?: '-' }}
                        </td>

                    </tr>


                    <tr>

                        <td class="label-cell">
                            Work Performed
                        </td>

                        <td>
                            {{ $report->work_performed ?: '-' }}
                        </td>

                    </tr>


                    <tr>

                        <td class="label-cell">
                            Parts Used
                        </td>

                        <td>
                            {{ $report->parts_used ?: '-' }}
                        </td>

                    </tr>


                    <tr>

                        <td class="label-cell">
                            Remarks
                        </td>

                        <td>
                            {{ $report->remarks ?: '-' }}
                        </td>

                    </tr>

                </table>


                {{-- Technician Attachments --}}

                @if($report->attachments->count())

                    <strong>
                        Technician Attachments:
                    </strong>

                    <ul>

                        @foreach(
                            $report->attachments as $attachment
                        )

                            <li>
                                {{ $attachment->original_name ?? '-' }}
                            </li>

                        @endforeach

                    </ul>

                @endif


                {{-- Department Confirmation --}}

                @if($report->confirmation)

                    <div class="avoid-break">

                        <strong>
                            Department Confirmation:
                        </strong>

                        {{
                            $report->confirmation->is_resolved === 'Yes'
                            ? 'Confirmed Resolved'
                            : 'Not Resolved'
                        }}


                        @if($report->confirmation->feedback)

                            <br>

                            <strong>
                                Feedback:
                            </strong>

                            {{
                                $report
                                    ->confirmation
                                    ->feedback
                            }}

                        @endif


                        <br>

                        <strong>
                            Confirmed By:
                        </strong>

                        {{
                            $report
                                ->confirmation
                                ->confirmedBy?->name
                            ?? '-'
                        }}

                    </div>

                @endif


            </div>

        @endif

    @endforeach

@endforeach


@if($reportNumber === 0)

    <p class="muted">
        No Technical Officer work reports recorded.
    </p>

@endif


{{-- ============================================================
     6. ESCALATION INFORMATION
============================================================ --}}

<div class="section-title">
    6. Escalation Information
</div>


<table class="details">

    <tr>

        <td class="label-cell">
            Forwarded By
        </td>

        <td>
            {{ $escalation->forwardedBy?->name ?? '-' }}
        </td>

        <td class="label-cell">
            Forwarded At
        </td>

        <td>

            {{
                $escalation->forwarded_at
                ? $escalation
                    ->forwarded_at
                    ->format(
                        'd F Y - h:i A'
                    )
                : '-'
            }}

        </td>

    </tr>


    <tr>

        <td class="label-cell">
            Escalation Type
        </td>

        <td colspan="3">
            {{ $escalation->trigger_type ?? '-' }}
        </td>

    </tr>

</table>


<strong>
    Reason for Escalation:
</strong>

<div class="description-box">
    {{ $escalation->reason ?: '-' }}
</div>


{{-- ============================================================
     7. IT ADMINISTRATOR FINAL DECISION
============================================================ --}}

<div class="section-title">
    7. IT Administrator Final Decision
</div>


<div class="decision-box">

    <div class="decision-title">
        Final Administrative Decision
    </div>


    <table class="details">

        <tr>

            <td class="label-cell">
                Decision
            </td>

            <td>
                <strong>
                    {{ $escalation->admin_decision ?? '-' }}
                </strong>
            </td>

        </tr>


        <tr>

            <td class="label-cell">
                IT Administrator Remarks
            </td>

            <td>
                {{ $escalation->admin_remarks ?: '-' }}
            </td>

        </tr>


        <tr>

            <td class="label-cell">
                Reviewed By
            </td>

            <td>
                {{ $escalation->reviewedBy?->name ?? '-' }}
            </td>

        </tr>


        <tr>

            <td class="label-cell">
                Reviewed At
            </td>

            <td>

                {{
                    $escalation->reviewed_at
                    ? $escalation
                        ->reviewed_at
                        ->format(
                            'd F Y - h:i A'
                        )
                    : '-'
                }}

            </td>

        </tr>


        <tr>

            <td class="label-cell">
                Final Request Status
            </td>

            <td>
                <strong>
                    {{ $requestData->status ?? '-' }}
                </strong>
            </td>

        </tr>

    </table>

</div>


{{-- ============================================================
     8. ACTIVITY HISTORY
============================================================ --}}

<div class="section-title">
    8. Activity History
</div>


@if($requestData->activityLogs->count())

    <table class="details">

        <thead>

        <tr>

            <th style="width: 5%;">
                #
            </th>

            <th style="width: 20%;">
                Date / Time
            </th>

            <th style="width: 18%;">
                User
            </th>

            <th style="width: 22%;">
                Action
            </th>

            <th>
                Details
            </th>

        </tr>

        </thead>


        <tbody>

        @foreach(
            $requestData->activityLogs
                ->sortBy('created_at')
            as $index => $log
        )

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>

                    {{
                        $log->created_at
                        ? $log
                            ->created_at
                            ->format(
                                'd M Y - h:i A'
                            )
                        : '-'
                    }}

                </td>

                <td>
                    {{ $log->user?->name ?? 'System' }}
                </td>

                <td>
                    {{ $log->action ?? '-' }}
                </td>

                <td>
                    {{ $log->details ?: '-' }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

@else

    <p class="muted">
        No activity history recorded.
    </p>

@endif


{{-- ============================================================
     DECLARATION
============================================================ --}}

<div class="section-title">
    Report Declaration
</div>


<p class="small">

    This report was generated from the Complain Management
    System after completion of the IT Administrator review.
    It contains the complaint information and workflow records
    available in the system at the time of generation.

</p>


<table
    style="
        width: 100%;
        margin-top: 35px;
    "
>

    <tr>

        <td
            style="
                width: 45%;
                text-align: center;
            "
        >

            ______________________________

            <br>

            IT Administrator

            <br>

            <span class="small muted">
                Signature / Authorization
            </span>

        </td>


        <td style="width: 10%;"></td>


        <td
            style="
                width: 45%;
                text-align: center;
            "
        >

            ______________________________

            <br>

            Date

        </td>

    </tr>

</table>


<div class="footer-note">

    Complaint Report

    &nbsp; | &nbsp;

    Request No:
    {{ $requestData->request_number ?? '-' }}

    <br>

    Generated by the Complain Management System on
    {{ now()->format('d F Y - h:i A') }}

</div>


</body>
</html>