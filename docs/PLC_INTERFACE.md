# Telemetrie- en PLC-interface

| Veld | Waarde |
|---|---|
| Documentversie | 1.0 |
| Bijbehorende appversie | 0.2.0 |
| Contractstatus | Voorlopig; PLC-leverancier moet dit bevestigen |

## Telemetrie ontvangen

- Methode/pad: `POST /api/tracker/logs`
- Content-Type: `application/json`
- Authenticatie: `Authorization: Bearer <TRACKER_INGEST_TOKEN>`
- Succes: HTTP 201 met het opgeslagen record onder `data`.
- Ongeldige data: HTTP 422; ontbrekende/onjuiste bearer-token: HTTP 401.
- Alle kernmetingen zijn vereist. `datum_tijd` is optioneel; de database vult de actuele tijd in.
- `modus`, `systeem_actief` en `noodstop_actief` zijn optionele velden.

Voorbeeld van de canonieke interne velden:

```json
{
  "datum_tijd": "2026-10-17T12:30:00Z",
  "positie_actuator_1": 42.5,
  "positie_actuator_2": 44.0,
  "opbrengst": 8.7,
  "netto_opbrengst": 7.2,
  "verbruik_actuator_1": 0.4,
  "verbruik_actuator_2": 0.3,
  "motor_verbruik": 0.8,
  "windsnelheid": 3.1,
  "status": "active",
  "modus": "automatic",
  "systeem_actief": true,
  "noodstop_actief": false
}
```

### Payload aanpassen

`config/tracker.php` bevat `telemetry.field_map`, met per canoniek veld het externe JSON-pad. Geneste paden gebruiken puntnotatie. Als de leverancier bijvoorbeeld `plant.energy.total` gebruikt, wijzig dan:

```php
'opbrengst' => 'plant.energy.total',
```

Bekende enumeraties zijn instelbaar via `telemetry.value_map`, bijvoorbeeld `auto` naar `automatic` en `hand` naar `manual`. Daarna blijft validatie op de canonieke velden actief. Onbekende vormen falen gesloten met een validatiefout. Meeteenheden worden niet automatisch omgerekend; leg de bron- en canonieke eenheid vast voordat een conversie wordt toegevoegd.

## Commando's naar PLC-gateway

De app bouwt de bestemming uit `TRACKER_PLC_URL` plus `TRACKER_PLC_COMMAND_PATH` (standaard `api/commands`). Optioneel wordt `TRACKER_PLC_TOKEN` als bearer-token meegestuurd. Timeout: `TRACKER_PLC_TIMEOUT` seconden.

| Actie | JSON-body die de app verstuurt |
|---|---|
| Oost | `{"command":"oost"}` |
| West | `{"command":"west"}` |
| Poot omhoog | `{"command":"poot_omhoog"}` |
| Poot omlaag | `{"command":"poot_omlaag"}` |
| Automatische modus | `{"mode":"automatic","command":"set_mode"}` |
| Handmatige modus | `{"mode":"manual","command":"set_mode"}` |
| Noodstop | `{"command":"noodstop"}` |
| Noodstop reset | `{"command":"noodstop_reset"}` |

De modevelden staan in de request-body; volgorde van JSON-properties is niet contractueel. De PLC moet voor succes een 2xx-response teruggeven. De UI beschouwt die response uitsluitend als ontvangstbevestiging, niet als bewijs dat een actuator werkelijk bewoog.

## Open contractvragen

- Bevestig endpoint, TLS, authenticatie, payloadnamen, responsebody en foutcodes van de PLC-gateway.
- Bevestig dat `set_mode` expliciet zet en niet intern als toggle werkt.
- Bevestig `noodstop_reset`, de resetvoorwaarden en dat reset geen beweging start.
- Bevestig of de PLC status terugstuurt via telemetrie en wat `status`, `modus`, actief en noodstop precies betekenen.
- Bevestig payloadfrequentie, timestamp-tijdzone/precisie, meeteenheden en geldige bereiken.
- Verifieer het contract eerst met een simulator/testcontroller; `TRACKER_PLC_URL` staat momenteel niet ingesteld.