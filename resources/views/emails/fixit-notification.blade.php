{{-- Shared FixIT design for standard Laravel MailMessage notifications.
     Uses the existing subject, greeting, introLines, action and outroLines. --}}
@php
    $beforeDetails = [];
    $details = [];
    $afterDetails = [];
    $detailsStarted = false;

    foreach ($introLines ?? [] as $line) {
        // A field is a short "Label: Value" line. Normal prose stays a paragraph.
        if (preg_match('/^([A-Za-z][A-Za-z0-9 \/().-]{1,45}):\s*(.*)$/s', $line, $parts)) {
            $detailsStarted = true;
            $details[] = ['label' => trim($parts[1]), 'value' => trim($parts[2])];
        } elseif ($detailsStarted) {
            $afterDetails[] = $line;
        } else {
            $beforeDetails[] = $line;
        }
    }
@endphp

<x-mail::message>
<div style="font-family: Arial, Helvetica, sans-serif;">

<div style="color:#64748b; font-size:11px; font-weight:bold; letter-spacing:1px; margin-bottom:12px;">
    FIXIT NOTIFICATION
</div>

<h1 style="color:#0f172a; font-size:23px; font-weight:700; line-height:1.4; margin:0 0 20px;">
    {{ $subject ?? 'FixIT Notification' }}
</h1>

<p style="color:#0f172a; font-size:16px; font-weight:600; margin:0 0 18px;">
    {{ $greeting ?? 'Hello,' }}
</p>

@foreach ($beforeDetails as $line)
<p style="color:#475569; font-size:14px; line-height:1.7; margin:0 0 16px;">
    {{ $line }}
</p>
@endforeach

@if (count($details))
<table width="100%" cellpadding="0" cellspacing="0" role="presentation"
       style="width:100%; background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:9px; margin:20px 0;">
    <tr>
        <td style="padding:20px;">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                @foreach ($details as $index => $detail)
                <tr>
                    <td width="38%" valign="top" style="padding:10px 12px 10px 0; border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }}; color:#64748b; font-size:13px; line-height:1.5;">
                        {{ $detail['label'] }}
                    </td>
                    <td valign="top" style="padding:10px 0; border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }}; color:#0f172a; font-size:14px; font-weight:600; line-height:1.5; overflow-wrap:anywhere;">
                        {{ $detail['value'] }}
                    </td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
@endif

@foreach ($afterDetails as $line)
<p style="color:#475569; font-size:14px; line-height:1.7; margin:0 0 16px;">
    {{ $line }}
</p>
@endforeach

@if (!empty($actionText) && !empty($actionUrl))
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endif

@foreach ($outroLines ?? [] as $line)
<p style="color:#475569; font-size:14px; line-height:1.7; margin:0 0 16px;">
    {{ $line }}
</p>
@endforeach

<p style="color:#475569; font-size:13px; margin-top:22px;">
    {{ $salutation ?? 'FixIT - IT Issue Resolution System' }}
</p>

</div>
</x-mail::message>
