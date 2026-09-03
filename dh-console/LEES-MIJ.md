# Beheerpaneel voor de cases

Hiermee voeg je cases toe, wijzig je ze en verwijder je ze, zonder ooit een
HTML-bestand aan te raken. Het paneel draait op de eigen server; bezoekers
krijgen gewone statische pagina's te zien.

## Hoe het werkt

1. Je vult in het paneel een formulier in.
2. De gegevens gaan naar `data/cases.json`.
3. Bij opslaan schrijft het paneel meteen de HTML weg:
   - `portfolio/index.html` — de kaarten in het raster
   - `portfolio/<webadres>/index.html` — de casepagina zelf
4. Bezoekers laden dus nog steeds kant-en-klare HTML. Geen database, geen
   PHP tijdens het bezoek, geen tragere pagina's.

## Eenmalig installeren

1. Zet deze mappen op de server, naast `index.html`:
   - `dh-console/`
   - `data/`

   De map heet met opzet niet `beheer` of `admin`: die adressen worden door
   bots standaard afgelopen. Wil je een andere naam? Hernoem de map gewoon;
   de code leest zelf waar hij staat.
2. Zorg dat PHP 8 aanstaat (hPanel → Geavanceerd → PHP-configuratie).
3. Geef de webserver schrijfrechten op `data/`, `assets/` en `portfolio/`
   (map-rechten 755 volstaat meestal; bij twijfel 775).
4. Ga naar `https://dh-studio.nl/dh-console/` en kies een wachtwoord van minstens
   12 tekens. Dat kan maar één keer: daarna is de pagina afgeschermd.

Wachtwoord kwijt? Verwijder `data/wachtwoord.php` via FTP of de bestandsbeheerder
en stel het opnieuw in.

## Een case schrijven

Het formulier heeft drie delen: wat er op de portfoliopagina komt, wat er
bovenaan de casepagina staat, en het verhaal zelf.

Voor het verhaal gebruik je een paar tekens:

| Wat je typt              | Wat je krijgt                                    |
| ------------------------ | ------------------------------------------------ |
| `# Kop`                  | een hoofdstuk, met nummer en in de inhoudsopgave  |
| `## Kop`                 | een keuze met een eigen kopje                     |
| `> tekst`                | een uitspraak, groot uitgelicht                   |
| `- **Vet.** rest`        | een regel in een opsomming                        |
| `= 42 kB \| homepage`    | een kerngetal (meerdere regels worden één blok)   |
| lege regel               | nieuwe alinea                                     |
| `**vet**`                | vetgedrukt                                        |
| `[tekst](https://…)`     | een link                                          |

Alles wat je verder typt wordt als gewone tekst behandeld: HTML uit een
formulier komt nooit op de site terecht.

## Afbeeldingen

Upload een schermafbeelding van 1400 × 875 pixels (JPG, PNG of WebP, maximaal
5 MB). Het bestand komt terecht in `assets/werk-<webadres>.jpg`. Laat het veld
leeg om de bestaande afbeelding te houden.

## Waar de opmaak vandaan komt

De casepagina's worden gemaakt met `dh-console/sjablonen/case.html`. Daarin zit de
volledige opmaak van de site: koptekst, menu, stijl en voettekst. Verandert er
iets aan het ontwerp, dan pas je dat sjabloon aan en druk je in het paneel op
**Opnieuw publiceren**.

In `portfolio/index.html` staan twee markeringen:

    <!-- CASES:START -->  …  <!-- CASES:EIND -->
    <!-- SCHEMA:START --> …  <!-- SCHEMA:EIND -->

Alles daarbuiten mag je met de hand aanpassen; het paneel raakt het niet aan.
Alles daarbinnen wordt bij elke publicatie overschreven.

## Beveiliging

- Inloggen met een gehasht wachtwoord (`password_hash`), sessie gebonden aan
  het IP-adres.
- Na vijf mislukte pogingen is inloggen vijftien minuten geblokkeerd.
- Elk formulier is beveiligd tegen misbruik van buitenaf (CSRF-token).
- `data/`, `dh-console/lib/` en `dh-console/sjablonen/` zijn met `.htaccess` afgeschermd.
- Uploads worden gecontroleerd op echt beeldformaat, niet alleen op de naam.
- Bestanden worden weggeschreven via een tijdelijk bestand, dus een halve
  pagina kan niet ontstaan.

Zet het paneel alleen open via HTTPS.
