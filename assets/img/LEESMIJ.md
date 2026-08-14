# Afbeeldingen

## vdheide-service-techniek.webp

De sectie "Ons werk" laat de website van VD Heide Service & Techniek door een
browserframe scrollen. Dat is één lange screenshot van de hele pagina.

| | |
| --- | --- |
| Bron | Full-page screenshot, 1990 × 8062 px, 4,4 MB PNG |
| In gebruik | 1440 × 5834 px, 316 KB WebP |

De PNG is teruggeschaald naar 1440 px breed — breder toont het frame hem
nooit — en opgeslagen als WebP op kwaliteit 82. Dat scheelt ruim 93 procent
zonder zichtbaar verlies. De originele PNG zit nog in de git-geschiedenis,
maar staat bewust niet meer in de repo: 4,4 MB meesturen bij elke kloon is
zonde voor een bestand dat niemand opvraagt.

### Vervangen door een nieuwe versie

Verandert de site van de klant, maak dan een nieuwe full-page screenshot:

```bash
npx playwright screenshot \
  --full-page \
  --viewport-size=1440,900 \
  --wait-for-timeout=3000 \
  "https://darkred-hornet-552528.hostingersite.com/" \
  nieuwe-screenshot.png
```

Kan dat niet, dan lukt het ook via de browser: open de site, druk op **F12**,
dan **Ctrl+Shift+P** (Cmd+Shift+P op Mac), typ `screenshot` en kies
**Capture full size screenshot**.

Comprimeer daarna naar WebP voordat je hem toevoegt:

```bash
cwebp -q 82 -resize 1440 0 nieuwe-screenshot.png \
  -o assets/img/vdheide-service-techniek.webp
```

### Adresbalk

In `index.html` staat in het frame de tekst `vdheideservicetechniek.nl`. De
site draait nu nog op een tijdelijke Hostinger-URL. Zodra het echte domein
live staat klopt dit; gebeurt dat niet, pas de tekst dan aan naar wat een
bezoeker werkelijk in de adresbalk ziet.

## Hoe de scroll werkt

Zie `assets/js/main.js`, onderdeel 5b. De afbeelding schuift binnen een venster
met een vaste verhouding (16:10), gekoppeld aan de scrollpositie van de
bezoeker: staat de sectie onderin beeld, dan zie je de bovenkant van de site;
verlaat hij het scherm, dan is de onderkant bereikt. Omdat alleen `translate`
verandert, ontstaat er geen layout shift.

Mist het bestand, dan verschijnt automatisch een nette terugvaloptie met de
klantnaam in plaats van een gebroken beeld.
