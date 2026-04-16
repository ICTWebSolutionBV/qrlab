<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>You're invited to {{ $appName }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #111827; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
        .header { background: linear-gradient(135deg, #1f2937 0%, #111827 60%, #064e3b 100%); padding: 40px 40px 32px; text-align: center; }
        .logo { display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: #059669; border-radius: 14px; margin-bottom: 16px; }
        .logo svg { width: 32px; height: 32px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #374151; margin-bottom: 16px; line-height: 1.6; }
        .message { font-size: 15px; color: #6b7280; line-height: 1.7; margin-bottom: 28px; }
        .btn { display: block; width: fit-content; margin: 0 auto 28px; padding: 14px 32px; background: #059669; color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 15px; letter-spacing: 0.1px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .link-fallback { font-size: 13px; color: #9ca3af; line-height: 1.6; }
        .link-fallback a { color: #059669; word-break: break-all; }
        .expires { font-size: 13px; color: #9ca3af; text-align: center; margin-top: 8px; }
        .footer { padding: 20px 40px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="logo">
                <svg fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <h1>{{ $appName }}</h1>
        </div>

        <div class="body">
            <p class="greeting">
                Hi{{ $firstName ? ' ' . $firstName : '' }},
            </p>
            <p class="message">
                <strong>{{ $inviterName }}</strong> has invited you to join <strong>{{ $appName }}</strong> — a QR code generator that lets you create, manage, and track QR codes.
            </p>

            <a href="{{ $inviteUrl }}" class="btn">Accept Invitation</a>

            <p class="expires">This invitation expires on {{ $expiresAt->format('F j, Y \a\t g:i A') }}.</p>

            <hr class="divider" />

            <p class="link-fallback">
                If the button doesn't work, copy and paste this link into your browser:<br />
                <a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p>You received this email because someone invited you to {{ $appName }}.<br />If you weren't expecting this, you can safely ignore it.</p>
        </div>
    </div>
</div>
</body>
</html>
