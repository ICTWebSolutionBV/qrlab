<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feedback from {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#111827;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e5e7eb;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#111827;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#1f2937;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 8px 28px;">
                            <h1 style="margin:0 0 4px 0;font-size:20px;color:#ffffff;">New feedback</h1>
                            <p style="margin:0;color:#9ca3af;font-size:13px;">Submitted through {{ $appName }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 28px 8px 28px;">
                            <div style="background-color:#111827;border-radius:8px;padding:16px 18px;white-space:pre-wrap;color:#f3f4f6;font-size:14px;line-height:1.55;">{{ $messageBody }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:13px;color:#cbd5e1;">
                                @if ($submitterName || $submitterEmail)
                                    <tr>
                                        <td style="padding:4px 0;color:#9ca3af;width:120px;">Submitter</td>
                                        <td style="padding:4px 0;">{{ trim(($submitterName ?? '') . ' ' . ($submitterEmail ? '<' . $submitterEmail . '>' : '')) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0;color:#9ca3af;">Account</td>
                                    <td style="padding:4px 0;">{{ $userName ?? '—' }} &lt;{{ $userEmail ?? '—' }}&gt; · {{ $userRole ?? 'user' }}</td>
                                </tr>
                                @if ($pageUrl)
                                    <tr>
                                        <td style="padding:4px 0;color:#9ca3af;">From page</td>
                                        <td style="padding:4px 0;word-break:break-all;">{{ $pageUrl }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0 0;color:#6b7280;font-size:12px;">Reply directly to this email to respond to the submitter.</p>
            </td>
        </tr>
    </table>
</body>
</html>
