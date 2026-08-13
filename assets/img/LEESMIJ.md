# Afbeeldingen

## vdheide-service-techniek.png

De sectie "Ons werk" laat de website van VD Heide Service & Techniek door een
browserframe scrollen. Daarvoor is één lange screenshot van de hele pagina nodig.

**Dit bestand ontbreekt nog.** De bouwomgeving mocht `hostingersite.com` niet
benaderen, dus de screenshot kon hier niet gemaakt worden. Zolang het bestand
er niet is, toont de sectie een nette terugvaloptie in plaats van een gebroken
beeld — de pagina blijft dus gewoon werken.

### Zelf maken

Eén commando, vanuit de hoofdmap van dit project:

```bash
npx playwright screenshot \
  --full-page \
  --viewport-size=1440,900 \
  --wait-for-timeout=3000 \
  "https://darkred-hornet-552528.hostingersite.com/" \
  assets/img/vdheide-service-techniek.png
```

Werkt Playwright niet op je machine? Dan kan het ook met de browser zelf:
open de site, druk op **F12**, dan **Ctrl+Shift+P** (Cmd+Shift+P op Mac), typ
`screenshot` en kies **Capture full size screenshot**. Sla het bestand op onder
bovenstaande naam.

### Comprimeren

Een full-page PNG is al snel enkele megabytes. Zet hem om naar WebP — dat
scheelt meestal 70 tot 90 procent zonder zichtbaar verlies:

```bash
cwebp -q 82 assets/img/vdheide-service-techniek.png \
  -o assets/img/vdheide-service-techniek.webp
```

Pas daarna de `src` in `index.html` aan naar het `.webp`-bestand.

### Zodra de echte domeinnaam live staat

In `index.html` staat in de adresbalk van het frame `vdheideservicetechniek.nl`.
Draait de site nog op de tijdelijke Hostinger-URL, pas die tekst dan aan zodat
het frame klopt met wat een bezoeker daadwerkelijk ziet.

## Hoe de scroll werkt

Zie `assets/js/main.js`, onderdeel 5b. De afbeelding schuift binnen een venster
met een vaste verhouding (16:10), gekoppeld aan de scrollpositie: staat de
sectie onderin beeld, dan zie je de bovenkant van de site; scrolt hij eruit,
dan is de onderkant bereikt. Omdat alleen `translate` verandert, kost het niets
aan layout en ontstaat er geen layout shift.
