# Changelog

Notable project changes are recorded here. Versions use semantic versioning while the app is pre-1.0.

## [0.2.0] - 2026-10-17

### Added

- Configurable telemetry field paths and enum normalization in `config/tracker.php`.
- Feature coverage for nested sender payloads while preserving canonical validation.

## [0.1.0] - 2026-10-17

### Added

- Public read-only tracker status and authenticated operator controls.
- MAMP MySQL telemetry storage, protected ingestion API, and repeatable 14-day demo data.
- Zoomable solar-opbrengst chart with selectable date ranges.
- Ten-minute operator-inactivity scheduler with an explicit automatic-mode PLC request.
- Mode/status telemetry, asynchronous control feedback, and confirmed emergency-stop/reset UI.
- Operator provisioning and password-reset console commands.
- Ongoing bug report log in `BUG_REPORT.md`.

### Changed

- Net yield remains in the latest telemetry KPI/table; the chart plots solar opbrengst only.
- MySQL uses the MAMP service on port 8889; tests use isolated in-memory SQLite.

### Safety Notes

- The PLC must enforce mechanical limits and safety interlocks.
- Verify the PLC's `set_mode` and `noodstop_reset` payloads before enabling hardware commands.
- This Laravel 11 prototype is local-only; dependency advisories require upgrades before deployment.