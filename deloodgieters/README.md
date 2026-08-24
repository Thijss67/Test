# deloodgieters.nl — nieuwe website (Van Renselaar)

Eén statische pagina: `index.html`. Geen build, geen dependencies — openen of uploaden en klaar.

## Vóór livegang aanpassen

| Wat | Waar |
|---|---|
| E-mailadres (nu placeholder `info@deloodgieters.nl`) | zoek/vervang op `info@deloodgieters.nl` (7×, incl. JSON-LD) |
| Telefoonnummer | zoek/vervang op `tel:+31641223984` en `06 41 22 39 84` |
| Adres: footer/contact tonen **Gerstekamp 12** — op de huidige contactpagina staat Gerstekamp 17 | zoek op `Gerstekamp` |
| Foto van Gert bij de bus (nu illustratie) | blok `.about__fig` in `index.html`, comment wijst de weg |
| Privacyverklaring | link in de footer (`privacyverklaring.html`) |
| Bereikbaarheid/openingstijden | contactblok, regel "Ma t/m vr, plus spoed" |

## Thema wisselen

Alle kleuren zitten in CSS-tokens bovenin `index.html`. Er staan drie varianten klaar:

| Thema | Sfeer | Zetten |
|---|---|---|
| **leisteen** (standaard) | grafietblauw + gedempte klei, wit op de CTA — rustig | niets doen |
| **messing** | petrol + amber, donkere tekst op de CTA — feller | `<html lang="nl" data-theme="messing">` |
| **koper** | inktblauw + koper | `<html lang="nl" data-theme="koper">` |

Een eigen variant maak je door één tokenblok te kopiëren en de waarden aan te passen —
verderop in het bestand staan geen losse kleurcodes meer, ook niet in de illustraties.

De CTA is in elk thema dezelfde knop: gevulde accentkleur, iets donkerder rand voor een
zichtbare begrenzing, zachte ring eromheen en in de hero één maat groter. De secundaire
mailknop blijft bewust rustig, zodat er per blok één knop is die de aandacht pakt.

Contrast van het standaardthema is nagerekend: wit op de CTA 4,8:1, kopjes 6,1:1,
bijschriften 5,5:1, accenttekst op donker 7,3:1 — allemaal ruim boven de WCAG AA-grens.

## Mobile first

De stylesheet is mobile first geschreven: alle basisregels gelden voor het kleinste scherm,
grotere schermen worden opgebouwd met uitsluitend `min-width` media queries. Er staat geen
enkele `max-width` breakpoint in het bestand (op één `.mbar`-detail na voor zeer smalle telefoons).

Breakpoints: **sm 560px · md 720/780/820px · lg 900px · xl 1140px**

Wat dat concreet betekent:

- **Telefoon**: CTA staat direct onder de intro (kenmerken volgen daarna), knoppen op volle
  breedte, minimaal 48px aanraakhoogte, vaste actiebalk onderin met bellen en mailen.
- **Header** scrollt op telefoon en tablet gewoon mee — de actiebalk houdt contact bereikbaar —
  en de sectienavigatie is een horizontaal scrollbare strip die tot beide schermranden loopt.
  Vanaf 1140px past merk + navigatie + belknop op één regel; dan pas wordt de header sticky
  en verdwijnt de actiebalk.
- **Reviews** zijn op telefoon een swipebare carrousel met scroll-snap (volgende kaart piept
  ernaast), vanaf 900px een raster van drie.
- Rasters schalen 1 → 2 → 3 kolommen; hover-effecten staan in `@media (hover: hover)` zodat
  ze niet blijven hangen na een tik.
- Getest van 360px tot 1440px: geen horizontale overflow, CTA boven de vouw op elk formaat.

## Uitgangspunten van het ontwerp

- **Binnen 5 seconden duidelijk**: H1 noemt beroep + plaats + de drie klussen waarvoor mensen bellen;
  de Google-score (4,9/5, 92 reviews) staat boven de vouw.
- **Precies twee CTA's, overal dezelfde tekst**: "Bel 06 41 22 39 84" (`tel:`) en
  "Stuur een e-mail" (`mailto:`). Geen andere knopteksten naar dezelfde bestemming.
- **Reviews direct zichtbaar**: badge in de hero, reviewsectie meteen onder de hero,
  rest uitklapbaar.
- Toegankelijkheid: skip-link, zichtbare focus, semantische koppen, `prefers-reduced-motion`.
- SEO: title/description, canonical, Open Graph en `Plumber`-schema met `aggregateRating`.

Reviewteksten komen van Google; namen zijn overgenomen zoals geplaatst.
