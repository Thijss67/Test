# Media-assets

De site gebruikt twee korte filmloops, gegenereerd met Higgsfield
(Seedance 2.5, 1280×720, 5 seconden, zonder audio nodig).

| Bestand | Waar | Beeld |
| --- | --- | --- |
| `hero-loop.mp4` | Hero, achter de titel | Vloeibaar kwik en elektrisch blauw licht over zwart glas |
| `statement-loop.mp4` | "Je beoordeelt DH Studio nu al op deze website" | Lichtbundel die langzaam door een donker, minimalistisch interieur strijkt |

**Deze map is nu nog leeg.** De bouwomgeving mocht de Higgsfield-CDN niet
benaderen, dus de bestanden konden niet automatisch worden opgehaald.
`index.html` verwijst daarom voorlopig rechtstreeks naar de CDN. De site
werkt intussen volledig: zonder video blijft de CSS-achtergrond staan.

## Zelf afronden

Download beide bestanden één keer:

```bash
mkdir -p assets/media

curl -o assets/media/hero-loop.mp4 \
  "https://d8j0ntlcm91z4.cloudfront.net/user_3Hrk8NfAcGxoW6V1aEHtFRQ6QqO/hf_20260813_192701_598fa339-f9a0-4f06-b8ed-355cd7af58d3.mp4"

curl -o assets/media/statement-loop.mp4 \
  "https://d8j0ntlcm91z4.cloudfront.net/user_3Hrk8NfAcGxoW6V1aEHtFRQ6QqO/hf_20260813_193621_e0308670-2d10-4be6-ba3d-e4d464c4373a.mp4"
```

Vervang daarna in `index.html` de twee CDN-URL's door de lokale paden:

```html
<div class="hero-film" id="heroFilm" data-video="assets/media/hero-loop.mp4"></div>
<div class="film" data-video="assets/media/statement-loop.mp4"></div>
```

Dit is geen detail: een generatie-CDN is geen hosting. Die URL's kunnen
verlopen, en dan staat je hero stil.

## Comprimeren (doen)

De ruwe export is te zwaar voor een achtergrondloop. Haal de audiospoor
eruit — die wordt toch nooit afgespeeld — en comprimeer stevig:

```bash
ffmpeg -i hero-loop.mp4 -an -c:v libx264 -crf 30 -preset slow \
  -movflags +faststart -vf "scale=1600:-2" hero-loop.min.mp4
```

Een WebM ernaast scheelt nog eens flink in Chrome en Firefox:

```bash
ffmpeg -i hero-loop.mp4 -an -c:v libvpx-vp9 -crf 40 -b:v 0 hero-loop.webm
```

Meerdere bronnen mogen achter elkaar in `data-video`, gescheiden door `|`.
De loader probeert ze op volgorde en gebruikt de eerste die speelt:

```html
data-video="assets/media/hero-loop.webm | assets/media/hero-loop.min.mp4"
```

## Hoe de loader zich gedraagt

Zie `assets/js/main.js`. Bewust terughoudend:

- niet bij `prefers-reduced-motion`
- niet bij databesparing of een 2G-verbinding
- pas zodra de sectie in beeld komt (200px vooruit)
- mislukt een bron, dan volgt automatisch de volgende
- mislukken ze allemaal, dan gebeurt er zichtbaar niets

De films komen met een fade in beeld, op 55% (hero) en 40% (statement),
onder een scrim. Daardoor blijft het tekstcontrast gegarandeerd — ongeacht
wat er in het beeld gebeurt.
