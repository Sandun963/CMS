<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Complaint Requests Report</title>

    <style>

        /*
        |--------------------------------------------------------------------------
        | Liberation Serif
        |--------------------------------------------------------------------------
        | Used ONLY for the Division name inside the official header.
        */

        @font-face {
            font-family: 'LiberationSerif';
            font-style: normal;
            font-weight: normal;
            src: url('{{ public_path("fonts/LiberationSerif-Regular.ttf") }}')
                format('truetype');
        }


        /*
        |--------------------------------------------------------------------------
        | General PDF Styling
        |--------------------------------------------------------------------------
        */

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Official Header
        |--------------------------------------------------------------------------
        */

        .official-header {
            width: 100%;
            text-align: center;
            margin: 0;
            padding: 0;
            margin-top: -30px;
        }

        .official-header img {
            display: block;
            width: 70%;
            height: auto;
            margin: 0;
            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Division Name
        |--------------------------------------------------------------------------
        | Liberation Serif is used ONLY here.
        |
        | IMPORTANT:
        | Do not use position:absolute here.
        | DomPDF was placing the division at the bottom of the page.
        |
        | The negative margin moves the normal-flow text upward into
        | the blank section at the bottom of nreport.png.
        */

        .header-division {
            font-family: 'LiberationSerif', serif;
            font-size: 17px;
            font-weight: normal;
            color: #000000;

            text-align: center;
            text-transform: uppercase;

            line-height: 1;

            /*
             * Pull Division upward into header image blank area.
             */
            margin-top: -28px;

            /*
             * Space between header and report title.
             */
            margin-bottom: 18px;

            padding: 0;
            position: relative;
            left: -30px;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Report Title
        |--------------------------------------------------------------------------
        */

        .report-title {
            text-align: center;

            margin-top: 0;
            margin-bottom: 12px;

            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            font-weight: bold;

            color: #222;

            /* Move title left/right */
            position: relative;
            left: -30px;
        }


        /*
        |--------------------------------------------------------------------------
        | Generated Information
        |--------------------------------------------------------------------------
        */

        .generated-date {
            text-align: right;

            margin-bottom: 10px;

            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;

            color: #666;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | Applied Filters
        |--------------------------------------------------------------------------
        */

        .filters {
            margin-bottom: 15px;
            padding: 8px;

            background: #f5f5f5;

            border: 1px solid #dddddd;

            line-height: 1.7;
        }


        /*
        |--------------------------------------------------------------------------
        | Report Table
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            border-collapse: collapse;

            font-family: DejaVu Sans, sans-serif;
        }

        th {
            background-color: #941e3b;

            color: white;

            padding: 7px;

            border: 1px solid #cccccc;

            text-align: center;

            vertical-align: middle;
        }

        td {
            padding: 6px;

            border: 1px solid #cccccc;

            text-align: center;

            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: #f5f5f5;
        }


        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 15px;

            text-align: center;

            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;

            color: #777;
        }

    </style>

</head>

<body>


{{-- ========================================================= --}}
{{-- OFFICIAL HEADER                                           --}}
{{-- ========================================================= --}}

<div class="official-header">

    <img
        src="{{ public_path('images/nreport.png') }}"
        alt="Chief Secretary's Office ICT Unit"
    >

</div>


{{-- ========================================================= --}}
{{-- LOGGED-IN USER'S DIVISION                                 --}}
{{-- ========================================================= --}}

@if(auth()->user()->division)

    <div class="header-division">
        {{ auth()->user()->division->name }}
    </div>

@else

    {{-- Keep normal spacing when the user has no Division --}}
    <div style="margin-bottom: 18px;"></div>

@endif


{{-- ========================================================= --}}
{{-- REPORT TITLE                                              --}}
{{-- ========================================================= --}}

<div class="report-title">
    Complaint Requests Report
</div>


{{-- ========================================================= --}}
{{-- GENERATED INFORMATION                                     --}}
{{-- ========================================================= --}}

<div class="generated-date">

    Generated:
    {{ now('Asia/Colombo')->format('d F Y - h:i A') }}

    <br>

    Exported By:
    {{ auth()->user()->name }}

</div>


{{-- ========================================================= --}}
{{-- APPLIED FILTERS                                           --}}
{{-- ========================================================= --}}

@if(
    !empty($filters['request_number'])
    || !empty($filters['status'])
    || !empty($filters['floor_id'])
    || !empty($filters['division_id'])
    || !empty($filters['area_id'])
    || !empty($filters['department_user_id'])
    || !empty($filters['technician_id'])
    || !empty($filters['date_from'])
    || !empty($filters['date_to'])
)

    <div class="filters">

        <strong>Applied Filters:</strong>

        <br>


        @if(!empty($filters['request_number']))

            Request Number:
            {{ $filters['request_number'] }}

            &nbsp;&nbsp;

        @endif


        @if(!empty($filters['status']))

            Status:
            {{ $filters['status'] }}

            &nbsp;&nbsp;

        @endif


        @if($selectedFloor)

            Floor:
            {{ $selectedFloor->name }}

            &nbsp;&nbsp;

        @endif


        @if($selectedDivision)

            Division:
            {{ $selectedDivision->name }}

            &nbsp;&nbsp;

        @endif


        @if($selectedArea)

            Area:
            {{ $selectedArea->name }}

            &nbsp;&nbsp;

        @endif


        @if($selectedDepartmentUser)

            Department User:
            {{ $selectedDepartmentUser->name }}

            &nbsp;&nbsp;

        @endif


        @if($selectedTechnician)

            Technical Officer:
            {{ $selectedTechnician->name }}

            &nbsp;&nbsp;

        @endif


        @if(!empty($filters['date_from']))

            From:
            {{ $filters['date_from'] }}

            &nbsp;&nbsp;

        @endif


        @if(!empty($filters['date_to']))

            To:
            {{ $filters['date_to'] }}

        @endif

    </div>

@endif


{{-- ========================================================= --}}
{{-- REPORT TABLE                                              --}}
{{-- ========================================================= --}}

<table>

    <thead>

        <tr>

            <th>
                Request No.
            </th>

            <th>
                Floor
            </th>

            <th>
                Division
            </th>

            <th>
                Area
            </th>

            <th>
                Category
            </th>

            <th>
                Problem
            </th>

            <th>
                Requested By
            </th>

            <th>
                Technical Officer
            </th>

            <th>
                Status
            </th>

            <th>
                Submitted Date
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($requests as $request)

            <tr>


                <td>

                    {{ $request->request_number }}

                </td>


                <td>

                    {{
                        $request->floor?->name
                        ?? $request->department?->floor
                        ?? '-'
                    }}

                </td>


                <td>

                    {{
                        $request->division?->name
                        ?? $request->department?->name
                        ?? '-'
                    }}

                </td>


                <td>

                    {{
                        $request->areaLocation?->name
                        ?? $request->area
                        ?? '-'
                    }}

                </td>


                <td>

                    {{ $request->category?->name ?? '-' }}

                </td>


                <td>

                    {{ $request->title }}

                </td>


                <td>

                    {{ $request->requestedBy?->name ?? '-' }}

                </td>


                <td>

                    {{ $request->assignedTo?->name ?? 'Not Assigned' }}

                </td>


                <td>

                    {{ $request->status }}

                </td>


                <td>

                    {{ $request->created_at->format('d/m/Y') }}

                </td>


            </tr>

        @empty

            <tr>

                <td
                    colspan="10"
                    style="text-align:center;"
                >

                    No breakdown requests found.

                </td>

            </tr>

        @endforelse

    </tbody>

</table>


{{-- ========================================================= --}}
{{-- FOOTER                                                    --}}
{{-- ========================================================= --}}

<div class="footer">

    Chief Secretary's Office -
    ICT Unit

</div>


</body>

</html>