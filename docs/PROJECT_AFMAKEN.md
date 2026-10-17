# Project afronden: openstaande acties

Deze lijst beschrijft wat nog nodig is om van het lokale prototype naar een gevalideerd en beheerd project te gaan. De huidige status is **niet geschikt voor publieke deployment of hardwarecommissioning**.

## P0 — Vereist vóór echte hardwarebediening

- [ ] Vast PLC HTTP-contract ontvangen: base URL, pad, TLS, auth, requests, responses, foutcodes en timeouts.
- [ ] Bevestigen dat PLC expliciet `set_mode` met `automatic`/`manual` ondersteunt.
- [ ] Bevestigen dat `noodstop` en `noodstop_reset` bestaan; documenteer interlocks en resetvoorwaarden.
- [ ] Hardwarematig aantonen dat PLC onafhankelijk 35°–55°, fysieke eindstops, windveiligheid en alle interlocks afdwingt.
- [ ] Vastleggen wat operatorinactiviteit betekent; bevestigen dat auto-mode na 10 minuten operationeel gewenst en veilig is.
- [ ] Stale telemetry/heartbeat en gedrag bij communicatieverlies vastleggen; UI moet oude status uiteindelijk als stale tonen.
- [ ] PLC-simulator of testcontroller gebruiken voor contracttests; daarna formele FAT/SAT en ondertekende commissioning door bevoegden.
- [ ] Gevaaranalyse uitvoeren met machineveiligheidsdeskundige; deze softwarenotitie is niet formeel of gecertificeerd.

## P1 — Vereist vóór betrouwbare telemetry

- [ ] Leverancier levert voorbeeldpayloads voor alle bedrijfstoestanden, fouten, ontbrekende/null-velden en grenswaarden.
- [ ] Per veld eenheid, datatype, bereik, precisie, timezone, samplefrequentie en semantiek vastleggen.
- [ ] `config/tracker.php` op echte bronvelden instellen; unitconversies afzonderlijk ontwerpen en testen.
- [ ] Definieer retry-/idempotencystrategie voor dubbele of vertraagde telemetry; nu wordt elk geldig POST als nieuwe meting opgeslagen.
- [ ] Bevestig formule voor `netto_opbrengst` en maak duidelijk of dashboard de laatste waarde of periode-aggregaat moet tonen.
- [ ] Kies retention-, back-up-, restore- en datakwaliteitsregels voor `tracker_logs`.

## P1 — Vereist vóór publieke of productie-inzet

- [ ] Laravel upgraden naar een ondersteunde, gepatchte release; daarna `composer audit` oplossen.
- [ ] Frontenddependency-advisories oplossen; Tailwind 3→4-migratie beoordelen en volledig testen.
- [ ] MAMP-defaultcredentials vervangen; aparte least-privilege MySQL-account maken; tokens roteren en buiten repository/broncode beheren.
- [ ] Productieconfiguratie vastleggen: `APP_DEBUG=false`, HTTPS, secure cookies, rate limits, firewall/VPN en logging/monitoring.
- [ ] TLS-verificatie en netwerktoegang van app naar PLC expliciet vastleggen; alleen noodzakelijke verbindingen toestaan.
- [ ] Scheduler als beheerde service/process manager installeren, bewaken en alarmen configureren; `schedule:work` draait nu niet.
- [ ] Herstel-, incident-, operator-aanmaak-, wachtwoordreset- en noodbedieningsprocedures testen.
- [ ] Privacy-/retentievereisten beoordelen voor accounts, IP-adressen en operationele logs.

## P2 — Productafronding

- [ ] Producteigenaar, PLC-eigenaar, beheerder en goedkeurders benoemen.
- [ ] UI-teksten, Nederlandse terminologie, units en tijdzone goedkeuren.
- [ ] Stale mode/status, verbindingsstatus en laatste succesvolle PLC-communicatie zichtbaar maken.
- [ ] Acceptatiecriteria en releaseprocedure met versie-/changeloggoedkeuring vastleggen.
- [ ] Documenten na leverancierbevestiging bijwerken en laten reviewen.

## Vrijgavebesluit

Totdat alle P0-acties zijn gesloten en door verantwoordelijke hardware-/veiligheidseigenaren zijn ondertekend, blijft dit een lokaal softwareprototype. De webapp vervangt geen PLC-safetylogica of hardwired noodstop.