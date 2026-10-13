
<x-mail::message>
<div style="
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1px;
    color: #64748b;
    margin-bottom: 12px;">
    TASK NOTIFICATION
</div>

# New Task Assigned

Dear {{ $officerName }},

A new IT support task has been assigned to you.
Please review the details below.

<table width="100%" cellpadding="0" cellspacing="0"
       role="presentation"
       style="
           background-color: #f8fafc;
           border: 1px solid #e2e8f0;
           border-radius: 10px;
           margin: 20px 0;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" cellpadding="0"
                   cellspacing="0" role="presentation">
                <tr>
                    <td width="50%" style="
                        padding: 0 12px 18px 0;
                        vertical-align: top;">
                        <div style="
                            color: #64748b;
                            font-size: 11px;">
                            REQUEST NUMBER
                        </div>
                        <div style="
                            color: #0f172a;
                            font-size: 14px;
                            font-weight: bold;
                            padding-top: 5px;">
                            {{ $requestNumber }}
                        </div>
                    </td>

                    <td width="50%" style="
                        padding-bottom: 18px;
                        vertical-align: top;">
                        <div style="
                            color: #64748b;
                            font-size: 11px;">
                            DUE DATE
                        </div>
                        <div style="
                            color: #0f172a;
                            font-size: 14px;
                            font-weight: bold;
                            padding-top: 5px;">
                            {{ $dueDate }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="
                        padding-bottom: 18px;">
                        <div style="
                            color: #64748b;
                            font-size: 11px;">
                            ISSUE
                        </div>
                        <div style="
                            color: #0f172a;
                            font-size: 14px;
                            font-weight: bold;
                            padding-top: 5px;">
                            {{ $issue }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="
                        padding-bottom: 18px;">
                        <div style="
                            color: #64748b;
                            font-size: 11px;">
                            ASSIGNED BY
                        </div>
                        <div style="
                            color: #0f172a;
                            font-size: 14px;
                            padding-top: 5px;">
                            {{ $assignedBy }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <div style="
                            color: #64748b;
                            font-size: 11px;">
                            ASSIGNMENT NOTE
                        </div>
                        <div style="
                            color: #0f172a;
                            font-size: 14px;
                            padding-top: 5px;">
                            {{ $note }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<x-mail::button :url="$taskUrl">
View Assigned Task
</x-mail::button>

Please review and complete the assigned work before
the due date.

Regards,  
**FixIT - IT Issue Resolution System**
</x-mail::message>
