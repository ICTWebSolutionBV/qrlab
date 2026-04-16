<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>You're invited to {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#111827;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <!-- Outer wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#111827;min-height:100vh;">
        <tr>
            <td align="center" style="padding:48px 16px;">

                <!-- Card -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;">

                    <!-- Logo + name header -->
                    <tr>
                        <td align="center" style="padding-bottom:28px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="vertical-align:middle;padding-right:12px;">
                                        <!-- Icon box -->
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="width:48px;height:48px;background-color:#059669;border-radius:12px;text-align:center;vertical-align:middle;">
                                                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiB2aWV3Qm94PSIwIDAgMjQgMjQiIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCI+PHBhdGggZD0iTTEyIDR2MW02IDExaDJtLTYgMGgtMnY0bTAtMTF2M20wIDBoLjAxTTEyIDEyaDQuMDFNMTYgMjBoNE00IDEyaDRtMTIgMGguMDFNNSA4aDJhMSAxIDAgMDAxLTFWNWExIDEgMCAwMC0xLTFINWExIDEgMCAwMC0xIDF2MmExIDEgMCAwMDEgMXptMTIgMGgyYTEgMSAwIDAwMS0xVjVhMSAxIDAgMDAtMS0xaC0yYTEgMSAwIDAwLTEgMXYyYTEgMSAwIDAwMSAxek01IDIwaDJhMSAxIDAgMDAxLTF2LTJhMSAxIDAgMDAtMS0xSDVhMSAxIDAgMDAtMSAxdjJhMSAxIDAgMDAxIDF6Ii8+PC9zdmc+" width="28" height="28" alt="" style="display:block;margin:10px auto;" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span style="font-size:26px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">{{ $appName }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- White card -->
                    <tr>
                        <td style="background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.4);">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">

                                <!-- Card body -->
                                <tr>
                                    <td style="padding:36px 40px 32px;">
                                        <p style="font-size:20px;font-weight:700;color:#111827;margin:0 0 8px;">You're invited!</p>
                                        <p style="font-size:15px;color:#6b7280;line-height:1.7;margin:0 0 28px;">
                                            Hi{{ $firstName ? ' ' . e($firstName) : '' }},<br /><br />
                                            <span style="color:#374151;font-weight:600;">{{ e($inviterName) }}</span> has invited you to join <span style="color:#374151;font-weight:600;">{{ $appName }}</span> — a QR code platform for creating, managing, and tracking QR codes.
                                        </p>

                                        <!-- CTA button -->
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding-bottom:24px;">
                                                    <a href="{{ $inviteUrl }}" style="display:inline-block;padding:14px 40px;background-color:#111827;color:#ffffff;text-decoration:none;border-radius:12px;font-size:15px;font-weight:600;letter-spacing:0.1px;">Accept Invitation</a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Expiry -->
                                        <p style="font-size:13px;color:#9ca3af;text-align:center;margin:0 0 24px;">
                                            This invitation expires on <span style="color:#6b7280;font-weight:500;">{{ $expiresAt->format('F j, Y \a\t g:i A') }}</span>.
                                        </p>

                                        <!-- Divider -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr><td style="border-top:1px solid #e5e7eb;padding-bottom:20px;"></td></tr>
                                        </table>

                                        <!-- Fallback link -->
                                        <p style="font-size:12px;color:#9ca3af;line-height:1.6;margin:0;">
                                            If the button doesn't work, paste this link into your browser:<br />
                                            <a href="{{ $inviteUrl }}" style="color:#059669;word-break:break-all;">{{ $inviteUrl }}</a>
                                        </p>
                                    </td>
                                </tr>

                                <!-- Card footer -->
                                <tr>
                                    <td style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:16px 40px;border-radius:0 0 20px 20px;">
                                        <p style="font-size:12px;color:#9ca3af;text-align:center;margin:0;line-height:1.6;">
                                            You received this because someone invited you to {{ $appName }}.<br />If you weren't expecting this, you can safely ignore it.
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    <!-- Bottom copyright -->
                    <tr>
                        <td align="center" style="padding-top:24px;">
                            <p style="font-size:12px;color:#4b5563;margin:0;">&copy; {{ date('Y') }} ICTWebSolution B.V. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
