<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Complain Requests Report
    </title>

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #941e3b;
        }

        .header p {
            margin-top: 5px;
            color: #666;
        }

        .generated-date {
            text-align: right;
            margin-bottom: 10px;
            font-size: 9px;
            color: #666;
        }

        .filters {
            margin-bottom: 15px;
            padding: 8px;
            background: #f5f5f5;
            border: 1px solid #dddddd;
            line-height: 1.7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .footer {
            margin-top: 15px;
            text-align: center;
            color: #777;
            font-size: 9px;
        }

    </style>

</head>

<body>


<div class="header">

    <h2>
        IT Complain Management System
    </h2>

    <p>
        Complain Requests Report
    </p>

</div>


<div class="generated-date">

    Generated:
    {{ now('Asia/Colombo')->format('d F Y - h:i A') }}

    <br>

    Exported By:
    {{ auth()->user()->name }}

</div>

@if(
    !empty($filters['request_number'])
    || !empty($filters['status'])
    || !empty($filters['floor_id'])
    || !empty($filters['division_id'])
    || !empty($filters['area_id'])
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


<table>

    <thead>

        <tr>

            <th>Request No.</th>

            <th>Floor</th>

            <th>Division</th>

            <th>Area</th>

            <th>Category</th>

            <th>Problem</th>

            <th>Requested By</th>

            <th>Technical Officer</th>

            <th>Status</th>

            <th>Submitted Date</th>

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


<div class="footer">

    Western Province Provincial Council -
    IT Department

</div>


</body>

</html>
