
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FixIT Daily Overdue Summary</title>
</head>
<body style="font-family: Arial, sans-serif; color: #243247; line-height: 1.6;">

    <h2 style="color: #175b96;">
        FixIT - Daily Overdue Summary
    </h2>

    <p>Dear IT Administrator,</p>

    <p>
        The following technical assignments remain overdue
        as of <strong>{{ $summaryDate }}</strong>.
    </p>

    <p>
        <strong>Total Overdue Assignments: {{ $total }}</strong>
    </p>

    <table style="border-collapse: collapse; width: 100%; font-size: 13px;"
           border="1" cellpadding="8">
        <thead style="background-color: #eaf2fa;">
            <tr>
                <th>Request No.</th>
                <th>Issue</th>
                <th>Technical Officer</th>
                <th>Assign Officer</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jobs as $job)
                <tr>
                    <td>
                        @if ($job['url'])
                            <a href="{{ $job['url'] }}">
                                {{ $job['request_number'] }}
                            </a>
                        @else
                            {{ $job['request_number'] }}
                        @endif
                    </td>
                    <td>{{ $job['title'] }}</td>
                    <td>{{ $job['technical_officer'] }}</td>
                    <td>{{ $job['assign_officer'] }}</td>
                    <td>{{ $job['due_date'] }}</td>
                    <td style="text-align: center;">
                        {{ $job['days_overdue'] }}
                    </td>
                    <td>{{ $job['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Please review these cases and coordinate the
        necessary follow-up actions with the responsible officers.
    </p>

    <p>
        Regards,<br>
        <strong>FixIT - IT Issue Resolution System</strong>
    </p>

</body>
</html>
