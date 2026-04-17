# Changelog

All notable changes to QR Lab are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Versioning policy

- **Major (`X.0.0`)** — Breaking changes to routes, database schema, or configuration that require manual intervention.
- **Minor (`1.X.0`)** — New user-facing features, backwards-compatible.
- **Patch (`1.0.X`)** — Bug fixes, performance improvements, copy/UI tweaks.

Every push that ships production-visible changes should bump the appropriate segment and add a dated entry below.

---

## [Unreleased]

_Nothing yet._

## [1.0.1] — 2026-04-17

### Added
- Screenshots section in the README covering QR generator, dashboard (grid + list), per-QR analytics, super-admin stats, user management, profile, and login.
- Captured screenshots under `docs/screenshots/`.

### Changed
- Repository made public on GitHub.

## [1.0.0] — 2026-04-17

First tagged release. QR Lab is now a full self-hosted QR platform with multi-type generation, scan analytics, role-based administration, and a super-admin platform dashboard.

### Added

#### QR code types & generation
- WiFi, URL, Phone, Email, and vCard QR code types.
- Public QR generator on `/` with live client-side preview.
- Styled generation: dot styles, corner styles, colors, error-correction levels.
- Per-QR logo upload stored privately on disk and served through an authenticated route (`GET /qr/{qrCode}/logo`).
- Logos automatically relocated to a per-QR folder and cleaned up on delete (including bulk delete).
- Text labels with font picker, markup, and custom per-side margins.
- Optional WiFi credential info panel.
- Schemeless URLs auto-prepended with `https://` on save.
- Server-side export to PNG, SVG, and EPS; client-side download as PNG/JPG/SVG.

#### Scan tracking & analytics
- Short-link tracking redirects via `/r/{shortCode}`.
- Per-QR analytics dashboard with timeline chart, totals, and device/country breakdowns.
- Optional IP-based geolocation captured on scan.
- Tracking toggle always visible; automatically disabled for non-trackable types.

#### Dashboard
- Grid and list views with toggle, persisted in localStorage.
- Search, type filter, tracking filter, sortable columns.
- Fluid auto-fill card grid that scales from 4 → 3 → 2 → 1 columns across breakpoints.
- Bulk selection with confirmation modal and bulk delete action.
- Show QR modal for quick preview from the dashboard.

#### Authentication & 2FA
- Mandatory two-factor authentication (enforced in non-local environments).
- Supported factors: TOTP authenticator apps, email OTP codes, and WebAuthn passkeys.
- Two-step invite signup wizard: credentials → 2FA enrollment.
- Invite-only access with single-use tokens and HTML email templates styled to match the app.
- Password reset via email, plus admin-initiated reset links.
- Admin-initiated 2FA reset that forces re-enrollment on next sign-in.
- `TWO_FACTOR_ENABLED` config flag to skip 2FA locally.

#### Roles & administration
- Three-tier role model: `user`, `admin`, `super_admin`.
- Admins can create/edit users, send/revoke invites, reset passwords, reset 2FA.
- Super admins can grant/revoke the `super_admin` role and access a platform-wide stats dashboard.
- Regular admins cannot delete or demote existing super admins.
- `/admin/stats` super-admin dashboard with totals, 30-day scan trend (Chart.js), sortable/searchable per-user usage table with pagination, and top 10 QR codes globally.
- `php artisan user:promote <email> <role>` for CLI role management.

#### Profile & preferences
- Light/dark/auto theme toggle, persisted per user.
- Timezone preference (24 common IANA zones, defaults to `Europe/Amsterdam`).
- Configurable date/time format (DD-MM-YYYY, MM/DD/YYYY, ISO, etc.) with 24h or 12h time and optional seconds.
- Shared `Intl.DateTimeFormat`-based formatter used across the app.
- Passkey registration and revocation from the profile page.

#### Infrastructure
- Private filesystem disk (`storage/app/private`) for QR logo storage.
- Inertia shared props expose auth user (including role and date/time prefs) to every page.
- Super admin middleware alias registered in `bootstrap/app.php`.

### Fixed
- Bulk delete silently failing because QR code IDs are ULIDs, not integers.
- Missing private filesystem disk caused logo uploads to fail and the QR code not to save.
- Logos not displayed on the edit page after save.
- Music icon incorrectly shown for the analytics shortcut; replaced with bar-chart icon.
- Dashboard card sizing and density iterated across breakpoints to balance readability and information density.

[Unreleased]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/ICTWebSolutionBV/qrlab/releases/tag/v1.0.0
