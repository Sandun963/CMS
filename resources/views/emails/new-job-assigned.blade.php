
<x-mail::message>

<div style="color:#64748b; font-size:11px; font-weight:bold; letter-spacing:1px; margin-bottom:12px;">
    TASK NOTIFICATION
</div>

# New Task Assigned

Dear {{ $officerName }},

A new IT support task has been assigned to you.
Please review the details below.


<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin:20px 0;">
<tr>
<td style="padding:20px;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="padding:10px 0; color:#64748b; width:38%;">Request Number</td>
<td style="padding:10px 0; color:#0f172a; font-weight:bold;">{{ $requestNumber }}</td>
</tr>
<tr>
<td style="padding:10px 0; color:#64748b;">Issue</td>
<td style="padding:10px 0; color:#0f172a; font-weight:bold;">{{ $issue }}</td>
</tr>
<tr>
<td style="padding:10px 0; color:#64748b;">Assigned By</td>
<td style="padding:10px 0; color:#0f172a; font-weight:bold;">{{ $assignedBy }}</td>
</tr>
<tr>
<td style="padding:10px 0; color:#64748b;">Due Date</td>
<td style="padding:10px 0; color:#0f172a; font-weight:bold;">{{ $dueDate }}</td>
</tr>
<tr>
<td style="padding:10px 0; color:#64748b;">Assignment Note</td>
<td style="padding:10px 0; color:#0f172a;">{{ $note }}</td>
</tr>
</table>
</td>
</tr>
</table>


<x-mail::button :url="$taskUrl">
View Assigned Task
</x-mail::button>

Please review and complete the assigned work before the due date.

Regards,  
**FixIT - IT Issue Resolution System**

</x-mail::message>
