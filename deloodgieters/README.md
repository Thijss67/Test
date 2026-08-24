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

## Uitgangspunten van het ontwerp

- **Binnen 5 seconden duidelijk**: H1 noemt beroep + plaats + de drie klussen waarvoor mensen bellen;
  de Google-score (4,9/5, 92 reviews) staat boven de vouw.
- **Precies twee CTA's, overal dezelfde tekst**: "Bel 06 41 22 39 84" (`tel:`) en
  "Stuur een e-mail" (`mailto:`). Geen andere knopteksten naar dezelfde bestemming.
- **Reviews direct zichtbaar**: badge in de hero, reviewsectie meteen onder de hero,
  rest uitklapbaar.
- Vaste actiebalk onderaan op mobiel, sticky belknop in de header op desktop.
- Toegankelijkheid: skip-link, zichtbare focus, semantische koppen, `prefers-reduced-motion`.
- SEO: title/description, canonical, Open Graph en `Plumber`-schema met `aggregateRating`.

Reviewteksten komen van Google; namen zijn overgenomen zoals geplaatst.
