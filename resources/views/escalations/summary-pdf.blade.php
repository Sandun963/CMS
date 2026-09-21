<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Escalated Cases Summary Report
    </title>

    <style>

        @page {
            size: A4 portrait;
            margin: 12mm 12mm 15mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
            padding: 0;
        }


        /* =====================================================
           REPORT HEADER
        ===================================================== */

        .report-header {
            width: 100%;
            margin-bottom: 12px;
            text-align: center;
        }

        .report-header img {
            width: 100%;
            height: auto;
            display: block;
        }


        /* =====================================================
           REPORT INFO
        ===================================================== */

        .report-info {
            margin-bottom: 10px;
            text-align: right;
            font-size: 8px;
            color: #555;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background-color: #8b1734;
            color: #ffffff;
            border: 1px solid #666;
            padding: 7px 5px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
        }

        td {
            border: 1px solid #aaa;
            padding: 6px 5px;
            vertical-align: top;
            line-height: 1.4;
            word-wrap: break-word;
        }


        /* =====================================================
           COLUMN WIDTHS
        ===================================================== */

        .col-id {
            width: 16%;
        }

        .col-division {
            width: 27%;
        }

        .col-category {
            width: 20%;
        }

        .col-problem {
            width: 37%;
        }


        /* =====================================================
           OTHER
        ===================================================== */

        .empty {
            text-align: center;
            padding: 15px;
            color: #666;
        }

        .footer {
            margin-top: 15px;
            padding-top: 7px;
            border-top: 1px solid #bbb;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

    </style>

</head>


<body>


    {{-- =====================================================
         OFFICIAL HEADER
    ====================================================== --}}

    <div class="report-header">

        <img
            src="{{ public_path('images/escalation-summary-header.png') }}"
            alt="Chief Secretary Office ICT Unit"
        >

    </div>


    {{-- =====================================================
         GENERATED DATE
    ====================================================== --}}

    <div class="report-info">

        Generated:
        {{ now()->format('d F Y - h:i A') }}

    </div>


    {{-- =====================================================
         ESCALATED CASES SUMMARY
    ====================================================== --}}

    <table>

        <thead>

            <tr>

                <th class="col-id">
                    ID
                </th>

                <th class="col-division">
                    Division
                </th>

                <th class="col-category">
                    Category
                </th>

                <th class="col-problem">
                    Problem Identified
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($summaryRows as $row)

                <tr>

                    <td>
                        {{ $row['request_number'] }}
                    </td>

                    <td>
                        {{ $row['division'] }}
                    </td>

                    <td>
                        {{ $row['category'] }}
                    </td>

                    <td>
                        {{ $row['problem_identified'] }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="4"
                        class="empty"
                    >
                        No escalated cases available.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <div class="footer">

        Escalated Cases Summary Report |
        Complain Management System

    </div>


</body>

</html>