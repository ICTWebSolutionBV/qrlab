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

## [1.4.1] — 2026-04-20

### Added
- **Resend invite** from the admin user management page. Expired invites now show a "Resend" action that rotates the token, extends expiry by 72 hours, and re-sends the invite email. Used invites cannot be resent.

## [1.4.0] — 2026-04-17

### Added
- **Decorative QR frames** — new frame picker in the create flow with 11 presets: no frame, scan-me (top/bottom), rounded card, banner-bottom, tag-notch, pill-with-icon, and four cursive "Scan me" + curved arrow variants (corners: BR, BL, TR, TL).
- Per-frame controls: border color, separate label color (auto-contrasting text via YIQ luminance), label text, font size, font family (9 options including cursive and Impact), adjustable label distance (0–40px slider), and optional icon from a curated Lucide set of 22 icons for the pill-icon style.
- Live preview reflects every control instantly; thumbnail grid in the picker renders each preset so users can see what they'll get before selecting.

### Fixed
- Frame-picker thumbnails and live preview now stay pixel-centered across all 11 presets (resolved flexbox blockification and class-collision bugs on the tag-notch, pill-icon, and arrow variants).

## [1.3.0] — 2026-04-17

### Added
- **In-app "What's new" modal** — new sidebar button below "Send feedback" opens a popup with release notes parsed straight from `CHANGELOG.md`. Renders each version with date, grouped sections (Added / Changed / Fixed), and optional screenshots per release.
- A `NEW` badge is shown next to the button until the user has opened the modal for the current `app.version`; dismissed state is persisted in `localStorage`.
- `ChangelogService` parses the Markdown changelog into structured data; `ChangelogController` exposes it at `GET /changelog` (JSON) and serves release screenshots via `GET /changelog/image/{file}` so images can live in `docs/screenshots/` (outside `public/`).
- `app.version` config value + Inertia-shared `app_version` so the frontend knows the current release.

### Documentation
- README: new **Environment variables** section documenting every meaningful `.env` setting (Application, Security/auth, Database, Mail, Cache/queues/storage, Optional services) with defaults, allowed values, and descriptions.

## [1.2.0] — 2026-04-17

### Added
- **Bulk import** of QR codes via CSV for all five types (URL, WiFi, Phone, vCard, Email). Each type has a downloadable CSV template with headers and an example row.
- Upload flow validates the file, stashes the parsed rows server-side, and shows a 5-row preview with per-row errors so users can sanity-check before confirming.
- New `QrCodeBatch` model + migration (`qr_code_batches` table, `batch_id` FK on `qr_codes` with `nullOnDelete`).
- `QrBulkImportService` handles schema definitions, CSV templating, parsing/validation, and import.
- `QrBulkController` with routes for create, template download, preview, discard, store, batch show, batch destroy, and ZIP export.
- Batch detail page lists every QR code in the batch with scan counts and edit links.
- **ZIP export per batch** — download every QR code as PNG, SVG, or EPS in a single archive. File names are slugified from the QR name with duplicate-safe suffixes.
- Dashboard shows batches as distinct "bulk cards" with stacked-layer styling. Section is collapsed by default; when a newly-created batch is detected on the next dashboard visit, it auto-expands once and is then marked as seen.
- Screenshots added: `bulk-import-upload.png`, `dashboard-batches.png`, `batch-detail.png`.

### Changed
- Dashboard QR list now excludes QR codes that belong to a batch — they live on the batch page instead.

## [1.1.0] — 2026-04-17

### Added
- In-app feedback widget: "Send feedback" button in the sidebar (above account info) opens a modal with optional name/email, required message, and up to 5 screenshot uploads (5 MB each).
- Feedback submissions are emailed to `FEEDBACK_EMAIL` (configurable via `config/mail.php`, falls back to `MAIL_FROM_ADDRESS`).
- Reply-to on feedback emails is set to the submitter when an email is provided; the email body includes account info, originating page URL, and any uploaded screenshots as attachments.
- `FeedbackMail` mailable, `FeedbackController`, `POST /feedback` route (authenticated, throttled 5/min).
- `.env.example` entry documenting `FEEDBACK_EMAIL`.

## [1.0.2] — 2026-04-17

### Changed
- Replaced personal name in dashboard screenshots with a generated name (Jordan Hayes) for the vCard demo QR.

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

[Unreleased]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.4.1...HEAD
[1.4.1]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/ICTWebSolutionBV/qrlab/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/ICTWebSolutionBV/qrlab/releases/tag/v1.0.0
