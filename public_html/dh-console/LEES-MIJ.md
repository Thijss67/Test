# Beheerpaneel voor cases en blogartikelen

Hiermee voeg je cases en blogartikelen toe, wijzig je ze en verwijder je ze,
zonder ooit een HTML-bestand aan te raken. Het paneel draait op de eigen server; bezoekers
krijgen gewone statische pagina's te zien.

## Hoe het werkt

1. Je vult in het paneel een formulier in.
2. De gegevens gaan naar `data/cases.json` en `data/artikelen.json`.
3. Bij opslaan schrijft het paneel meteen de HTML weg:
   - `portfolio/index.html` — de kaarten in het raster
   - `portfolio/<webadres>/index.html` — de casepagina zelf
   - `blog/index.html` — de artikelkaarten
   - `blog/<webadres>/index.html` — het artikel zelf
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
4. Ga naar `https://dhstudio.nl/dh-console/` en kies een wachtwoord van minstens
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

## Blogartikelen

Bovenin het paneel staan twee tabbladen: **Cases** en **Blogartikelen**. Voor een
artikel vul je titel, datum, label (het gekleurde tekstje op de kaart),
samenvatting, afbeelding en tekst in. Dezelfde opmaaktekens gelden.

Artikelen staan automatisch op datum, nieuwste bovenaan. Zolang er geen artikel
gepubliceerd is, houdt de blogpagina zichzelf uit Google en toont hij het blok
"De eerste artikelen komen eraan" — de tekst daarvan staat in
`dh-console/sjablonen/blog-leeg.html`. Zodra je het eerste artikel publiceert,
verdwijnt dat blok en mag Google de pagina indexeren.

## Afbeeldingen

Upload een afbeelding van 1400 × 875 pixels (JPG, PNG of WebP, maximaal 5 MB).
Die komt terecht in `assets/werk-<webadres>.jpg` (case) of
`assets/blog-<webadres>.jpg` (artikel). Laat het veld
leeg om de bestaande afbeelding te houden.

## Waar de opmaak vandaan komt

De casepagina's worden gemaakt met `dh-console/sjablonen/case.html`, de
artikelen met `dh-console/sjablonen/artikel.html`. Daarin zit de opmaak van
zo'n pagina: stijl en indeling. Verandert er iets aan het ontwerp, dan pas je
dat sjabloon aan en druk je in het paneel op **Opnieuw publiceren**.

### Het menu en de voettekst

Die staan **niet** in die twee sjablonen, maar apart:

    dh-console/sjablonen/kop.html    het menu bovenaan
    dh-console/sjablonen/voet.html   de voettekst onderaan

Pas je daar iets aan — een link erbij, een ander telefoonnummer — druk dan op
**Kop, voet en sitemap**. Die knop zet het in één keer op alle pagina's,
inclusief de cases en de artikelen.

Bewerk kop en voet dus nooit in een pagina zelf: bij de eerstvolgende keer dat
je op die knop drukt, is je wijziging weg.

Welk menu-item oplicht op welke pagina hoef je niet bij te houden. Dat volgt
uit de lijst in `dh-console/lib/site.php`: elke link die naar de pagina zelf
wijst, krijgt automatisch `aria-current="page"`, en onder `/diensten` klapt
het Diensten-menu open. Komt er een nieuwe vaste pagina bij, dan zet je die in
die lijst — dan staat hij meteen ook in `sitemap.xml`.

### Markeringen in de pagina's

In de pagina's staan markeringen die aangeven wat het paneel beheert:

    <!-- KOP:START -->       …  <!-- KOP:EIND -->        (alle pagina's)
    <!-- VOET:START -->      …  <!-- VOET:EIND -->       (alle pagina's)
    <!-- CASES:START -->     …  <!-- CASES:EIND -->      (portfolio/index.html)
    <!-- ARTIKELEN:START --> …  <!-- ARTIKELEN:EIND -->  (blog/index.html)
    <!-- ROBOTS:START -->    …  <!-- ROBOTS:EIND -->     (blog/index.html)
    <!-- SCHEMA:START -->    …  <!-- SCHEMA:EIND -->     (portfolio en blog)

Alles daarbuiten mag je met de hand aanpassen; het paneel raakt het niet aan.
Alles daarbinnen wordt bij elke publicatie overschreven.

## Zoekmachines

`robots.txt` staat vast in de hoofdmap en wijst naar `sitemap.xml`.

`sitemap.xml` wordt gemaakt door het paneel en mag je niet met de hand
bewerken. Hij bevat de vaste pagina's plus elke **zichtbare** case en elk
zichtbaar artikel. Zet je iets op verborgen, dan verdwijnt het bij de
volgende publicatie ook uit de sitemap. `/404.html` staat er nooit in, en
`/blog` pas zodra er een artikel is — daarvoor staat die pagina op noindex.

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
