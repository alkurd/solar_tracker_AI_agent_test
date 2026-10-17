# Voorlopige veiligheids- en risiconotitie

| Veld | Waarde |
|---|---|
| Documentversie | 1.0 |
| Datum | 2026-10-17 |
| Scope | Alleen software/webapp en integratiegrens |
| Status | Voorlopig; geen formele machine-risicobeoordeling of certificering |

## Belangrijke grens

Deze webapp is geen veiligheids-PLC, noodstopcircuit of gecertificeerd veiligheidscomponent. De PLC en onafhankelijke hardware moeten veilige toestanden afdwingen, ook wanneer de webapp, database, netwerkverbinding of stroom uitvalt. Dit document vervangt geen risicoanalyse door een bevoegde machineveiligheidsdeskundige en verklaart geen CE-conformiteit.

## Risicoregister

| Gevaar/scenario | Softwaremaatregel aanwezig | Vereiste maatregel buiten de app | Status |
|---|---|---|---|
| Onbevoegde bedieningsopdracht | Sessielogin; controleroutes achter auth; geen publieke registratie | Accountbeheer, sterke unieke wachtwoorden, least privilege, netwerkisolatie en security review | Deels open |
| Verkeerde modus door toggle | Expliciete `set_mode`-opdracht | PLC-contract moet expliciet-set semantiek bevestigen | Open |
| Actuator buiten 35°–55° | UI vermeldt de grens; webapp doet geen veiligheidsbeperking | Onafhankelijke PLC-limieten, fysieke eindstops en testbewijs | Open; PLC-eigendom |
| Wind, mechanische storing of onderhoud | Geen veiligheidsinterlock in webapp | PLC-/hardware-interlocks, veilige windstow, lock-out/tag-out en onderhoudsprocedure | Open; PLC-eigendom |
| Noodstop wordt niet uitgevoerd | UI toont pas actief na 2xx-ack; expliciete resetactie | Hardwired/veiligheidscircuit blijft leidend; test communicatieverlies en resetcondities | Open; geen hardwarevalidatie |
| Oude of misleidende mode/status | UI toont laatst ontvangen telemetrie | PLC moet bronstatus, timestamp en stale-data/heartbeat leveren; UI moet stale state later expliciet kunnen aangeven | Open |
| Inactiviteitstaak schakelt onverwacht | >600 s, expliciet automatisch verzoek, één verzoek per activiteitstijd; noodstop blokkeert taak | PLC moet verzoek valideren; Scheduler permanent en bewaakt draaien; operatorbeleid vastleggen | Open; PLC-URL niet ingesteld |
| Telemetrie verkeerd geïnterpreteerd | Configureerbare paden, enum-normalisatie en validatie | Leverancier moet units, bereiken, timezone en enumtabellen documenteren; geen impliciete unitconversie | Open |
| Credential- of frameworkcompromis | Bearer-tokenconfiguratie en auth | Laravel 11/frontend advisories oplossen, MAMP-defaults roteren, secrets beheren, HTTPS en netwerkbeveiliging | Open; niet publiek deployen |

## Huidige technische controls

- Alleen vaste, allowlisted PLC-commando's worden geaccepteerd.
- Controleroutes zijn geauthenticeerd en de browser vraagt bevestiging voor noodstop/reset.
- Telemetrie vereist een ingest-bearer-token en wordt server-side gevalideerd.
- Noodstopstatus wordt niet op actief gezet op basis van een mislukte PLC-request.
- Idle automation gebruikt geen `toggle_modus` en wordt onderdrukt zolang de noodstopstatus actief is.

## Verplichte vrijgavevoorwaarden

Geen hardwarecommissioning voordat het PLC-interfacecontract, onafhankelijke limieten/interlocks, noodstop/reset, verlies-van-communicatiegedrag en foutinjectietests door de verantwoordelijke PLC- en machineveiligheidsdeskundigen zijn goedgekeurd. Zie [PROJECT_AFMAKEN.md](PROJECT_AFMAKEN.md).