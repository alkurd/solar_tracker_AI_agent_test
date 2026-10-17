# Vergelijkingsrapport: Solar_tracker

| Veld | Waarde |
|---|---|
| Rapportversie | 1.0 |
| Datum | 2026-10-17 |
| Bron | `https://github.com/Ogulcan2005/Solar_tracker` (publiek) |
| Bronstatus tijdens controle | Eén Laravel-skeletcommit; geen releases gepubliceerd |

## Bevindingen

De bronrepository bevat een map `Solar_Tracker` met een Laravel 13.17-skelet en PHP `^8.3`. De geneste README is de standaard Laravel-README. De `routes/web.php` bevat alleen de standaard `/`-route naar `welcome`; de console-route is de standaard `inspire`-opdracht; de getoonde tests zijn Laravel-voorbeeldtests. Er zijn geen trackercontrollers, telemetriemodellen, PLC-commando's, tracker-migraties of tracker-safetytests aangetroffen.

De bronrepository heeft dus geen domeinfunctionaliteit voor de solar tracker die veilig overgenomen kan worden. De Laravel 13-versie is geen reden om onze Laravel 11-versie rechtstreeks te vervangen: de huidige app heeft expliciet een Laravel 11-beperking en er zijn dependency-advisories die vóór een release afzonderlijk moeten worden opgelost.

## Bruikbare verbetering

De Laravel 13-bootstrap in de bronrepository configureert API-exceptions als JSON op API-paden, ook als de client geen `Accept: application/json` meestuurt. Dat principe is bruikbaar voor onze telemetryclients. In versie 0.2.1 is dit onafhankelijk toegevoegd aan `bootstrap/app.php` met `shouldRenderJsonWhen`; er is geen broncode of vormgevingsasset gekopieerd.

## Licentie en hergebruik

Bij de bekeken repositorybestanden is geen expliciete projectlicentie aangetroffen; de standaard Laravel-README vermeldt alleen de frameworklicentie. Daarom is geen projectcode, asset of licentieclaim overgenomen. De geïmplementeerde foutafhandelingsregel is een kleine frameworkconfiguratie die op de lokale Laravel 11-API is getest.

## Verificatie in dit project

- Featuretest controleert HTTP 422 met JSON-validatiefouten zonder `Accept`-header.
- De API blijft dezelfde bearer-token- en canonieke veldvalidatie afdwingen.