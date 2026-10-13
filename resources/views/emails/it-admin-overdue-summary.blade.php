<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>FixIT - Daily Overdue Summary</title></head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#334155;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f1f5f9;"><tr><td align="center" style="padding:18px 10px 28px;">
<table width="760" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:760px;background:#ffffff;border:1px solid #e2e8f0;">
<tr><td style="background:#123b67;padding:28px 38px;">
<div style="color:#ffffff;font-size:32px;font-weight:800;line-height:1.2;">FixIT</div>
<div style="color:#dcebf9;font-size:14px;margin-top:6px;">IT Issue Resolution System</div>
<div style="width:48px;height:3px;background:#38bdf8;margin-top:16px;"></div>
</td></tr>
<tr><td style="padding:32px;">
<div style="color:#64748b;font-size:11px;letter-spacing:1px;font-weight:bold;">DAILY OVERDUE REPORT</div>
<h1 style="color:#0f172a;font-size:23px;line-height:1.4;margin:14px 0 22px;">Daily Overdue Summary</h1>
<p style="color:#0f172a;font-size:16px;font-weight:600;">Dear IT Administrator,</p>
<p style="color:#475569;font-size:14px;line-height:1.7;">The following technical assignments remain overdue as of <strong>{{ $summaryDate }}</strong>.</p>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f8fafc;border:1px solid #e2e8f0;margin:18px 0;">
<tr><td style="padding:16px 20px;color:#64748b;font-size:13px;">TOTAL OVERDUE ASSIGNMENTS</td><td align="right" style="padding:16px 20px;color:#123b67;font-size:25px;font-weight:bold;">{{ $total }}</td></tr></table>
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;font-size:12px;">
<thead><tr style="background:#eaf2fa;">
@foreach (['Request', 'Issue', 'Technical Officer', 'Assign Officer', 'Due Date', 'Days Overdue', 'Status'] as $heading)
<th align="left" style="padding:10px 7px;border-bottom:1px solid #dbe5ef;color:#123b67;">{{ $heading }}</th>
@endforeach
</tr></thead><tbody>
@foreach ($jobs as $job)
<tr>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">@if (!empty($job['url']))<a href="{{ $job['url'] }}" style="color:#1263a8;">{{ $job['request_number'] }}</a>@else{{ $job['request_number'] }}@endif</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">{{ $job['title'] }}</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">{{ $job['technical_officer'] }}</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">{{ $job['assign_officer'] }}</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">{{ $job['due_date'] }}</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;color:#b45309;font-weight:bold;">{{ $job['days_overdue'] }}</td>
<td style="padding:10px 7px;border-bottom:1px solid #e2e8f0;">{{ $job['status'] }}</td>
</tr>
@endforeach
</tbody></table>
<p style="color:#475569;font-size:14px;line-height:1.7;margin-top:22px;">Please review these cases and coordinate the necessary follow-up actions with the responsible officers.</p>
<p style="color:#475569;font-size:13px;margin-top:22px;">FixIT - IT Issue Resolution System</p>
</td></tr>
<tr><td align="center" style="background:#f8fafc;padding:24px;border-top:1px solid #e2e8f0;">
<div style="color:#64748b;font-size:12px;">This is an automated notification from FixIT.</div>
<div style="color:#94a3b8;font-size:11px;margin-top:8px;">&copy; {{ date('Y') }} FixIT &middot; IT Issue Resolution System</div>
</td></tr></table></td></tr></table></body></html>
