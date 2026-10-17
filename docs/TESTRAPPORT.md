# Testrapport

| Veld | Waarde |
|---|---|
| Rapportversie | 1.0 |
| Appversie | 0.2.0 |
| Datum | 2026-10-17 |
| Teststatus | Softwaretests geslaagd; hardware-/PLC-tests niet uitgevoerd |

## Uitgevoerde controles

- `php artisan test`: 19 tests geslaagd, 70 assertions.
- `npm run build`: Vite-build geslaagd.
- `php artisan migrate:status`: vijf migraties toegepast op de MAMP-appdatabase.
- `php artisan schedule:list`: idle-modustaak geregistreerd per minuut.
- `php artisan config:show tracker.telemetry`: veld- en waardemappings geladen.
- Browsercontrole: openbare status, versie `0.2.0`, KPI netto-opbrengst en grafiek met alleen bruto-opbrengst.
- Databasecontrole: 673 trackerrecords, geen `NULL` in `netto_opbrengst`; nieuwste demorecord was nul tijdens nachtelijke tijd.

## Geteste softwaregevallen

- Authenticated/unauthenticated toegang tot status en bediening.
- Geldige en ongeldige bearer-token voor telemetrie.
- Canonieke telemetrie en genest extern veldpad met enum-normalisatie.
- Seeder-idempotentie en 14-daagse demo-historie.
- Inactiviteitsgrens >600 seconden, herhaalpreventie en recente activiteit.
- Noodstopprioriteit, bevestigde state, reset, publieke alarmweergave en foutresponse.
- Expliciete PLC-modepayload en vaste Oost-command-forwarding.

## Niet getest / niet aangetoond

- Geen request is naar een echte PLC gestuurd; `TRACKER_PLC_URL` is niet ingesteld.
- Geen motor-, actuator-, hydrauliek-, noodstopcircuit-, eindstop-, windstow- of hardwaretest.
- Geen PLC-simulator/Hardware-in-the-Loop, netwerkuitsval-, stroomuitval- of veiligheidsvalidatie.
- Geen production security-/performance-/backup-restore- of browsermatrixvalidatie.
- Composer en npm dependency-advisories zijn bekend en niet opgelost; niet deployen.

## Bewijsstatus

Dit rapport bewijst alleen de genoemde lokale softwarechecks op de testdatum. Het is geen FAT/SAT-rapport, conformiteitsverklaring of vrijgave voor bediening van echte apparatuur.