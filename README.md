# DH Studio — website

Statische website. Geen build-stap, geen framework, geen dependencies: de map
zoals hij hier staat is de website. Uploaden of koppelen aan je host is genoeg.

## Bestanden

```
index.html            De volledige homepage
privacy.html          Privacyverklaring
404.html              Foutpagina
assets/css/site.css   Design system + alle styling
assets/js/site.js     Gedrag (menu, animaties, formulier, metingen)
assets/img/           Logo, favicon, deelafbeelding
robots.txt            Zoekmachine-instructies
sitemap.xml           Sitemap
site.webmanifest      App-icoon / installeerbaarheid
```

## Het idee achter deze site

DH Studio verkoopt websites, maar heeft (nog) geen publieke cases of reviews.
Daarom leunt deze site niet op geleend bewijs, maar op zichzelf: **"Deze website
is ons portfolio."** De sectie *Het bewijs* meet in de browser van de bezoeker
hoe snel deze pagina laadde en hoeveel hij woog, en zet daar de harde feiten
naast: geen trackers, geen cookies, geen externe verzoeken.

Dat is meteen de belangrijkste onderhoudsregel:

> **Die cijfers moeten waar blijven.** Voeg je Google Analytics, een chatwidget,
> een cookiebanner of externe lettertypen toe, dan kloppen "0 externe verzoeken"
> en "0 trackers & cookies" niet meer. Pas dan óók die sectie en de
> privacyverklaring aan. Het hele argument van deze pagina staat of valt met
> het feit dat het klopt.

De ms- en KB-waarden zijn live metingen (Navigation Timing API) en passen zich
vanzelf aan; die hoef je niet bij te werken.

## Nog invullen of bevestigen

| Wat | Status |
|---|---|
| **E-mailadres** | Overal staat `info@dh-studio.nl`, afgelezen van de huidige site. Klopt dat, of moet het een ander adres zijn? Eén zoek-en-vervang over `index.html`, `privacy.html`, `404.html`. |
| **Contactformulier** | Werkt nu via `mailto:` — het opent de mailclient van de bezoeker met alles ingevuld. Dat werkt overal zonder server, maar kost conversie: bezoekers zonder ingestelde mailclient haken af. Zodra er een backend of formulier-endpoint is, is dit één functie in `site.js` (`form.addEventListener("submit", …)`). |
| **KvK- en btw-nummer** | Ontbreekt bewust — ik heb ze niet. Voor een Nederlandse zakelijke website is vermelding van je KvK-nummer verplicht. Zet ze in de footer van `index.html` en in `privacy.html`. |
| **Engelse versie** | De huidige site heeft een NL/EN-schakelaar. Die zit hier nog niet in. |
| **Telefoonnummer** | Niet opgenomen omdat ik er geen heb. Een zichtbaar nummer verhoogt doorgaans het vertrouwen en de conversie — overweeg het toe te voegen bij Contact. |

## Prijzen aanpassen

De tarieven staan op drie plekken in `index.html` en moeten gelijk blijven:

1. De `<article class="plan">`-blokken in de sectie `#tarieven`
2. Het antwoord "Wat kost een website?" in de FAQ
3. De `hasOfferCatalog` in de JSON-LD bovenaan het bestand (die voedt Google)

Huidige waarden: Starter €995, Professional €1495, Business Enterprise €2495,
onderhoud vanaf €49/maand, maatwerk op aanvraag.

## Deployen

Elke statische host werkt (Vercel, Netlify, Cloudflare Pages, of gewoon FTP).

Twee dingen op de server instellen:

- **Compressie aan** (gzip of brotli). De pagina is 100 KB onverpakt en ~23 KB
  ingepakt. Bijna elke host doet dit standaard.
- **404-pagina** laten wijzen naar `/404.html`.

Cache-instelling die past bij deze opzet: `index.html` kort cachen, de bestanden
in `assets/` lang (ze veranderen alleen als jij ze verandert).

## Gecontroleerd

Alles hieronder is in Chromium nagemeten, niet aangenomen:

- 3 verzoeken totaal, geen enkel extern domein
- Geen console-fouten, geen mislukte verzoeken
- Geen horizontale overflow van 320 tot 2560 px
- Geen noemenswaardige layout shift (CLS < 0,02)
- Alle tekst haalt WCAG AA-contrast (4.5:1, of 3:1 voor grote koppen)
- Volledig leesbaar en bruikbaar met JavaScript uitgeschakeld
- Bedienbaar met het toetsenbord, skip-link als eerste tabstop
- `prefers-reduced-motion` schakelt alle beweging uit

## Techniek in het kort

- **Geen webfonts.** De site gebruikt de systeemletter (SF Pro op Apple, Segoe UI
  op Windows). Dat scheelt een externe download, voorkomt tekstflikkering bij het
  laden en is een deel van waarom deze pagina zo snel is. Wil je later een eigen
  huisletter, host die dan zelf en laad hem met `font-display: swap`.
- **Twee werelden, één systeem.** Donker is de basis; secties met de klasse
  `paper` herdefiniëren dezelfde CSS-variabelen naar licht. Componenten passen
  zich vanzelf aan — je hoeft nooit een tweede versie van een knop of kaart te
  maken.
- **Animaties zijn versiering, geen constructie.** Alles wat beweegt gebruikt
  `transform` en `opacity` (dus geen layout shift), en valt netjes terug als
  JavaScript of IntersectionObserver ontbreekt.
