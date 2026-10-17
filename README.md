# Solar Tracker Dashboard

A Laravel 11 control dashboard and telemetry API for a solar tracker with east-west rotation, a north-side actuator, a Siemens S7 PLC, and an inverter data logger.

## Security status

This workspace is intentionally kept on Laravel 11 as requested, for local prototype use only. Composer's audit reports four advisories in `laravel/framework`, including a high-severity issue. `npm audit` reports seven frontend build-tool advisories (five high, two moderate), primarily in the existing Tailwind 3 toolchain; the suggested fix is a Tailwind major upgrade. Dependency installation required `composer install --no-security-blocking`; do not deploy this version or expose it to an untrusted network. Upgrade and audit both dependency stacks before production use. A narrow PHP 8.5 compatibility handler filters Laravel 11's deprecated MySQL SSL constant warning without suppressing unrelated deprecations.

## Versions and release notes

The current app version is `0.2.1`, shown in the dashboard header and configured through `APP_VERSION` (default `0.2.1`). For each release, update `APP_VERSION` and add a dated section to [CHANGELOG.md](CHANGELOG.md). Keep newly reported defects and their resolutions in [BUG_REPORT.md](BUG_REPORT.md).

## Project documents

- [Project dossier](docs/PROJECTDOSSIER.md): scope, architecture, environments, and known limitations.
- [PLC/API interface](docs/PLC_INTERFACE.md): telemetry mapping, command payloads, and unconfirmed contract items.
- [Preliminary safety note](docs/VEILIGHEIDSRAPPORT.md): software controls, external safety responsibilities, and release blockers. It is not a certified machine risk assessment.
- [Test report](docs/TESTRAPPORT.md): verified software checks and tests not yet performed.
- [Project comparison](docs/VERGELIJKING.md): findings from the supplied GitHub repository and the adopted API error-handling pattern.
- [Project completion checklist](docs/PROJECT_AFMAKEN.md): separate list of outstanding engineering, safety, security, and commissioning work.

## Local setup

Requirements: PHP 8.2 or newer, Composer, MAMP with MySQL enabled, and PHP's `pdo_mysql` extension. MAMP's default MySQL TCP port is `8889`; confirm the port in your MAMP settings if it was customized.

1. Install the local prototype dependencies with `composer install --no-security-blocking`. The override is required only because Composer currently blocks the requested Laravel 11 release; it does not make the framework safe for deployment.
2. Copy `.env.example` to `.env` if `.env` does not already exist.
3. Install frontend dependencies and build assets with `npm install` and `npm run build`.
4. Create the `solar_tracker` database in MAMP's phpMyAdmin or another MySQL client.
5. Set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=8889`, `DB_DATABASE=solar_tracker`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to match your MAMP MySQL account. A default local MAMP setup commonly uses `root`/`root`; change these values if yours differ. Never reuse the web operator password or expose this default outside a local development machine.
6. Generate the application key with `php artisan key:generate`.
7. Set `TRACKER_INGEST_TOKEN` and the PLC connection values described below in `.env`.
8. Run `php artisan migrate` and `php artisan db:seed --class=TrackerDemoDataSeeder`.
9. Create an operator account with `php artisan tracker:create-operator`; the command prompts for a name, email, and confirmed password without echoing the password.
10. Start the local server with `php artisan serve`, then visit `http://127.0.0.1:8000` for the public read-only dashboard.

The public `/tracker` page shows read-only telemetry. Operator actions are isolated at `/tracker/control`, and both that page and every PLC command require an authenticated session. Login is throttled; there is no public registration route. Create the first operator account with `php artisan tracker:create-operator`, or pass its email and `--name` option; the command prompts for a confirmed password without echoing it. Reset an existing operator password with `php artisan tracker:reset-operator-password <email>`.

## PLC configuration

Set these environment values in `.env`:

- `TRACKER_PLC_URL`: base URL of the PLC HTTP API.
- `TRACKER_PLC_COMMAND_PATH`: command endpoint path, default `api/commands`.
- `TRACKER_PLC_TOKEN`: optional bearer token for the PLC API.
- `TRACKER_PLC_TIMEOUT`: request timeout in seconds, default `5`.
- `TRACKER_INGEST_TOKEN`: required bearer token for telemetry ingestion.

The dashboard posts a fixed JSON command such as `{"command":"oost"}` to the configured PLC endpoint. Supported command names are `oost`, `west`, `poot_omhoog`, `poot_omlaag`, `set_mode`, `noodstop`, and `noodstop_reset`. Mode changes send an explicit `{"command":"set_mode","mode":"automatic"}` or `{"command":"set_mode","mode":"manual"}` payload. The controller does not accept arbitrary command text from a request. Actions submit without a full-page refresh and display the PLC response inline.

The web application does not know the PLC's actual actuator units or position feedback contract. The PLC must independently enforce the 35°–55° leg travel range, end stops, and all safety interlocks. A successful HTTP response only means the endpoint accepted the command; verify the PLC protocol and physical behavior before connecting equipment.

## Idle mode automation

After successful operator login, the application records the last operator activity. Each control action resets the timer. Once the operator has been idle for more than 10 minutes, Laravel Scheduler sends one explicit `{"command":"set_mode","mode":"automatic"}` request; a failed request is retried on the next scheduler run. To run the scheduler locally, start `php artisan schedule:work` in a second terminal alongside the web server. The task is registered to run every minute.

The configured PLC endpoint must implement that explicit set-mode payload. The scheduler does not use `toggle_modus`, because a toggle could put an already-automatic tracker back into manual mode. The current `.env` has no PLC URL configured, so this feature cannot command hardware until the endpoint and its expected payload are confirmed. An emergency-stop reset similarly sends `{"command":"noodstop_reset"}`; verify this reset contract and all physical safety conditions with the PLC engineer. The UI only clears its alarm after the PLC acknowledges the reset. Start the scheduler only after verifying the PLC mapping and hardware safety behavior.

## Demo data and chart

Run `php artisan db:seed --class=TrackerDemoDataSeeder` to create or refresh 672 demo telemetry points at 30-minute intervals over 14 days. These rows are labeled `demo` and contain synthetic values; they are not measured inverter data. The public and operator dashboards chart `opbrengst` with 1D, 7D, 14D, and all-data ranges, zoom buttons, drag-to-zoom, pan, and touch pinch zoom. Ctrl+mouse-wheel zooms the time axis. `netto_opbrengst` remains in the telemetry table/KPI as its latest reading; it can legitimately be `0.00` at night while the history chart shows daytime production.

## Telemetry API

Send a JSON `POST` to `/api/tracker/logs` with `Authorization: Bearer <TRACKER_INGEST_TOKEN>` and a JSON content type. All metrics are required; `datum_tijd` is optional and defaults to the database's current timestamp.

```json
{
  "datum_tijd": "2026-10-17T12:30:00Z",
  "positie_actuator_1": 42.5,
  "positie_actuator_2": 44.0,
  "opbrengst": 8.7,
  "status": "active",
  "modus": "automatic",
  "systeem_actief": true,
  "noodstop_actief": false,
  "verbruik_actuator_1": 0.4,
  "verbruik_actuator_2": 0.3,
  "motor_verbruik": 0.8,
  "netto_opbrengst": 7.2,
  "windsnelheid": 3.1
}
```

A valid payload is stored in `tracker_logs` and returns HTTP 201. The optional `modus` must be `automatic` or `manual`; `systeem_actief` and `noodstop_actief` are booleans. The dashboard shows the latest actuator positions, net yield, wind speed, PLC-reported mode, active state, status, and timestamp without assuming measurement units.

Incoming field names and dotted JSON paths are configured in `config/tracker.php` under `telemetry.field_map`; keys on the left are the application's canonical fields and values on the right are paths in the sender payload. For example, change `opbrengst` to `plant.energy.total` if that is the PLC's JSON shape. `telemetry.value_map` can normalize known enum values such as `auto` to `automatic`. Validation still runs after mapping. This adapter does not guess or convert measurement units; add and test an explicit conversion if the sender's units differ.

## Checks

Run `npm run build` to compile frontend assets and `php artisan test` for tests. Tests use a separate in-memory SQLite database and do not touch the MySQL application database. The suite covers authentication, dashboard controls, demo seeding, idle automation, telemetry token validation/storage, operator creation/password reset, and PLC command forwarding.
