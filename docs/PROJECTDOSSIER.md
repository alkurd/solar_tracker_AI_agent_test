# Projectdossier Solar Tracker

| Veld | Waarde |
|---|---|
| Documentversie | 1.0 |
| Appversie | 0.2.0 |
| Datum | 2026-10-17 |
| Status | Werkend lokaal prototype; niet vrijgegeven voor veldgebruik |
| Eigenaar/productverantwoordelijke | Nog vast te leggen |
| PLC/integratieverantwoordelijke | Nog vast te leggen |

## Doel

Een Laravel-webapp voor openbare, alleen-lezen trackerstatus en afgeschermde operatorbediening. De app slaat inverter-/PLC-telemetrie op in MySQL en stuurt toegestane opdrachten door naar een geconfigureerde HTTP-PLC-gateway. De app communiceert niet rechtstreeks via het Siemens S7-protocol.

## Systeemgrens

- Webinterface: openbare status op `/tracker`; operatorbediening op `/tracker/control` achter authenticatie.
- Backend: Laravel 11, sessieauthenticatie, validatie, Eloquent en vaste PLC-commandallowlist.
- Database: lokale MAMP MySQL, schema `solar_tracker`, TCP-poort 8889.
- Testdatabase: SQLite in-memory; tests raken de lokale MySQL-data niet.
- Frontend: Blade en Vite, Chart.js met zoom/pan voor `opbrengst`.
- Telemetrie-ingang: `POST /api/tracker/logs`, beschermd met een bearer-token.
- Hardwarebesturing: HTTP-request naar een nog niet geconfigureerde PLC-gateway.

## Hoofdprocessen

1. De PLC/logger stuurt JSON naar de telemetry API.
2. `config/tracker.php` vertaalt externe paden en bekende enumwaarden naar canonieke applicatievelden.
3. De backend valideert de canonieke waarden en schrijft een `tracker_logs`-record.
4. De publieke en operatorpagina tonen de laatst bekende waarden. De grafiek toont alleen `opbrengst`; `netto_opbrengst` blijft in de KPI/tabel.
5. Een operatoropdracht wordt via de vaste allowlist doorgestuurd. Een niet-2xx PLC-antwoord wordt als fout getoond.
6. Laravel Scheduler kan na meer dan 10 minuten zonder operatoractiviteit een expliciete automatische-modusopdracht aanvragen.

## Versie en documentatie

- Appversie: `0.2.0`; wijzig `APP_VERSION`/de config-default en voeg per release een gedateerde sectie toe aan [CHANGELOG.md](../CHANGELOG.md).
- Defecten en compatibiliteitsverzoeken: [BUG_REPORT.md](../BUG_REPORT.md).
- Implementatiehandleiding en lokale setup: [README.md](../README.md).
- Nog uit te voeren werk: [PROJECT_AFMAKEN.md](PROJECT_AFMAKEN.md).

## Bekende beperkingen

- `TRACKER_PLC_URL` is leeg; er is geen echte PLC-communicatie getest.
- De PLC moet zelf hoeken, eindstops, windveiligheid en interlocks afdwingen.
- De telemetry-eenheden, tijdzone en betekenis van elk statusveld moeten met de leverancier worden bevestigd.
- Demo-records zijn synthetisch en hebben `status=demo`; gebruik ze niet als meetbewijs.
- Laravel 11 en de huidige frontendtoolchain hebben open dependency-advisories. Dit prototype is uitsluitend lokaal.