# DH Studio — website

Statische site. Geen build, geen dependencies: openen in een browser werkt,
en uploaden naar elke host werkt ook.

## Bestanden

```
index.html          Homepage
diensten.html       Diensten (ankers: #hosting, #zichtbaarheid, #onderhoud)
werkwijze.html      Werkwijze in zes stappen
tarieven.html       Drie pakketten plus maatwerk en onderhoud
over.html           Ontstaansverhaal, Thijs & Lucas, waar we voor staan
contact.html        Formulier plus veelgestelde vragen (anker: #faq)

assets/css/styles.css   Eén stylesheet voor alle pagina's
assets/js/main.js       Eén script voor alle pagina's
assets/img/             Screenshot van de case en de deelafbeelding
robots.txt              Verwijst naar de sitemap
sitemap.xml             Alle zes pagina's
```

## Een subpagina toevoegen

De pagina's delen geen templates — het is platte HTML, dus header en footer
staan in elk bestand. Dat klinkt omslachtig, maar het houdt de site
afhankelijkheidsvrij. Zo voeg je een pagina toe:

1. **Kopieer een bestaande subpagina** die qua opbouw het dichtst in de buurt
   komt. `over.html` is de eenvoudigste; `diensten.html` heeft rijen.

2. **Pas de `<head>` aan.** Vier dingen moeten mee:
   - `<title>` en `<meta name="description">`
   - `<link rel="canonical" href="https://dh-studio.nl/jouw-pagina.html">`
   - de `og:url`, `og:title` en `og:description`
   - het `BreadcrumbList`-blok onderaan de head: pas `name` en `item` aan

3. **Pas de paginakop aan** — het blok `<section class="page-head">`:
   kruimelpad, eyebrow, `<h1>` en de inleidende alinea.
   Houd het bij **één `<h1>` per pagina**; alles daaronder is `<h2>`/`<h3>`.

4. **Zet de pagina in de navigatie.** Die staat in álle bestanden, op drie
   plekken per bestand: `nav-desktop`, `mobile-menu-inner` en de footer.
   Geef de actieve link `aria-current="page"` mee.

5. **Voeg de pagina toe aan `sitemap.xml`** met een `<loc>`, `<lastmod>` en
   een `<priority>`.

Vergeet stap 4 en 5 niet: zonder navigatielink is de pagina voor bezoekers
onvindbaar, en zonder sitemapregel duurt het langer voordat Google hem oppikt.

## Bruikbare bouwstenen

Alles staat al in de stylesheet:

| Klasse | Waarvoor |
| --- | --- |
| `.section` | Sectie met de standaard verticale ruimte |
| `.shell` | Inhoud gecentreerd op maximaal 1200 px |
| `.section-head` | Eyebrow, kop en inleiding boven een sectie |
| `.reveal` | Verschijnt bij scrollen. Met `data-delay="1"` t/m `4` voor volgorde |
| `.teaser-grid` + `.teaser` | Kaarten die doorverwijzen naar een andere pagina |
| `.btn btn-primary` / `.btn-quiet` | Primaire en secundaire knop |
| `.text-link` | Tekstlink met pijl |
| `.cta-band` | Donker blok onderaan de pagina |
| `.faq-list` + `.faq-item` | Uitklapbare vragen |

## Contactformulier

Er is geen backend. Nu opent het formulier een vooringevulde mail. Zodra je
een endpoint hebt, zet je dat op het formulier in `contact.html`:

```html
<form id="contactForm" data-endpoint="https://..." novalidate>
```

Het script post er dan JSON naartoe en valt terug op mail als dat mislukt.

## Waar op te letten

- **Eén `<h1>` per pagina.** Twee koppen van hetzelfde niveau verzwakken je
  structuur voor zoekmachines.
- **Afbeeldingen comprimeren** voordat je ze toevoegt. Zie
  `assets/img/LEESMIJ.md` — de case-screenshot ging van 4,4 MB naar 316 KB.
- **Geen verzonnen cijfers.** Er staan bewust geen reviews, klantaantallen of
  resultaten op de site die niet te onderbouwen zijn.
- **Animaties respecteren `prefers-reduced-motion`.** Houd dat zo als je
  beweging toevoegt.

## Controleren voor je publiceert

```bash
npx http-server . -p 8080
```

Loop daarna elke pagina na op: geen console errors, geen horizontale
schuifbalk, en één `<h1>`.
