# Workspace Instructions

This is a Laravel 11 solar tracker control dashboard and telemetry API. The local app uses MAMP MySQL on port 8889; PHPUnit uses isolated in-memory SQLite. Keep changes aligned with Laravel conventions and preserve the fixed PLC command allowlist.

## Safety and dependency status

This workspace is a local-only prototype. Composer audit reports four advisories in `laravel/framework`, including a high-severity advisory, and Laravel 11 was retained at the user's explicit direction. Do not recommend or prepare deployment without first upgrading to a supported, patched Laravel release and auditing dependencies. The PLC, not the web application, must enforce actuator angle limits and hardware safety interlocks.

## Setup checklist

- [x] Created this workspace instruction file.
- [x] Scaffolded Laravel 11 in the workspace root.
- [x] Implemented tracker migrations, models, authentication, controls, telemetry API, and Blade views.
- [x] Added configurable telemetry field/value mapping in config/tracker.php to tolerate sender schema differences.
- [x] Added repeatable 14-day demo telemetry and a zoomable opbrengst chart.
- [x] Added a scheduler task to request automatic PLC mode after more than 10 minutes without operator activity; PLC endpoint must support explicit `set_mode` payload.
- [x] Kept net opbrengst in the telemetry KPI/table and charted solar opbrengst separately; added async controls and emergency confirmation/glow/reset.
- [x] Installed dependencies for this local-only prototype with Composer security blocking disabled after the user chose to keep Laravel 11.
- [x] npm audit reports seven frontend toolchain advisories; Tailwind 3 major upgrade deferred.
- [x] Configured MAMP MySQL on port 8889 and applied migrations; Homebrew MySQL was stopped after it was superseded.
- [x] Verified routes, migrations, and tests (19 passed, 70 assertions); filtered only Laravel 11's PHP 8.5 MySQL SSL constant deprecation.
- [x] Provisioned/reset the requested operator account through a hidden CLI password prompt; no public registration was added.
- [x] No additional VS Code extensions or task configuration are required.
- [x] Documentation is current in README.md.
- [x] Version notes are maintained in CHANGELOG.md; the app version is displayed in the dashboard header.
- [x] Started the confirmed local development server at http://127.0.0.1:8088.
