# Bug Report Log

Reported during dashboard review on 2026-10-17.

## BR-001: Public dashboard requires operator login

**Severity:** Medium  
**Status:** Resolved

**Observed:** Opening the site immediately redirected visitors to the sign-in page. A projected dashboard therefore exposed no tracker status to general viewers.

**Expected:** Visitors can inspect read-only telemetry; only authenticated operators can open the control panel or send PLC commands.

**Resolution:** `/tracker` now displays public read-only status. `/tracker/control` and all movement, mode, and emergency-stop POST routes remain behind authentication. The public page does not render control buttons.

**Verification:** Feature tests cover the public status page, protected control page, and authenticated controls.

## BR-002: No initial operator account could be created through the app

**Severity:** Medium  
**Status:** Resolved

**Observed:** A fresh installation had no user account, no public registration, and no documented first-account setup path, so an operator could not sign in.

**Expected:** A secure first operator account can be provisioned without opening public registration.

**Resolution:** Run `php artisan tracker:create-operator`. It prompts for name, email, and a confirmed password of at least 12 characters; name and email may also be passed as arguments. The password is entered without terminal echo. For an existing account, `php artisan tracker:reset-operator-password <email>` resets the password through the same hidden prompt. The requested existing operator account was reset successfully; no default web password is stored in source code.

**Verification:** A feature test runs the command and verifies the operator record is stored.

## BR-003: Net yield appeared empty while the chart showed production

**Severity:** Low  
**Status:** Resolved

**Observed:** The KPI showed the latest `netto_opbrengst`, which was zero for the newest nighttime demo sample, while the chart showed gross `opbrengst` over the previous days.

**Resolution:** The KPI/table explicitly shows the latest `netto_opbrengst` reading. The chart plots only gross `opbrengst`, as requested. Nighttime net yield is shown as `0.00`, not treated as missing data. MAMP inspection confirmed 673 records with no null `netto_opbrengst`; the newest demo sample is 0.00 at 02:30. The demo data also reports `modus=automatic` and `systeem_actief=true` so mode/status are visible.

## BR-004: PLC controls refreshed the page and gave no visible feedback

**Severity:** Medium  
**Status:** Resolved

**Observed:** Control buttons submitted regular forms, causing a full page refresh; an unconfigured or rejecting PLC made the action look like it did nothing.

**Resolution:** Control forms now submit asynchronously and show the PLC response or an explicit error inline without navigating or refreshing the page.

## BR-005: Emergency stop had no confirmation, active indication, or reset

**Severity:** High  
**Status:** Resolved in the UI; PLC reset contract must be verified

**Resolution:** Noodstop and reset both ask for confirmation. Only a successful PLC acknowledgement activates the red top/bottom bands and global emergency banner. An authenticated `noodstop_reset` request clears them only after PLC acknowledgement. The physical PLC endpoint must implement both command names before operation.

## BR-006: Current operating mode and system state were not visible

**Severity:** Medium  
**Status:** Resolved

**Resolution:** The dashboard shows the last PLC-reported mode and active/stopped state. Operators choose explicit Automatic or Manual requests rather than a toggle; the display waits for telemetry to confirm the actual mode. Telemetry accepts optional `modus`, `systeem_actief`, and `noodstop_actief` fields.

## FR-007: Configurable telemetry payload mapping

**Type:** Compatibility enhancement  
**Status:** Implemented in v0.2.0

External senders may use different field names or nested JSON. `config/tracker.php` now maps sender paths and normalizes configured enum values into canonical application fields before the existing validation runs. Unit conversion remains explicit and must be added if a sender reports different units.

## Database migration clarification

The first migration ran on SQLite because the initial `.env` used `DB_CONNECTION=sqlite`; Laravel defaulted to `database/database.sqlite`. The app was then migrated to Homebrew MySQL, but MAMP is the intended server. `.env` and `.env.example` now target MAMP's MySQL port `8889`; the `solar_tracker` schema was created there and all five migrations are applied. Homebrew MySQL, which had been started during setup, was stopped. The local MAMP default `root`/`root` credentials are in the ignored `.env`; replace them if MAMP uses a different account and never expose that default outside local development. PHPUnit uses isolated in-memory SQLite so tests cannot alter application data.