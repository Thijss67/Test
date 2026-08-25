# deloodgieters.nl — Van Renselaar

Eén statische pagina: `index.html`. Geen build, geen dependencies, geen externe assets
behalve de webfonts.

## Het ontwerpidee: de kantlijn

Eén rode lijn loopt van de bovenrand tot de onderrand van de pagina. Alles hangt aan die
lijn — koppen, tekst, beeld, de voet. In de marge ernaast staat alleen de nummering van
de blokken en, in de hero, het adres van de site verticaal gezet. Zoals de rode kantlijn
in een werkboek.

Dat ene gebaar draagt de hele site. Daardoor zijn er geen omlijstingen, kaders of
kaartjes nodig om structuur te maken, en heeft de pagina een vorm die niet uit een
templatebibliotheek komt.

Verder in het systeem:

- **De kop als drieklank.** `LEKKAGE. VERSTOPPING. INSTALLATIEWERK.` — drie woorden
  onder elkaar in brede kapitalen, het derde in menierood. Binnen twee seconden duidelijk
  wat er te halen valt.
- **De belbalk.** De primaire actie is geen knopje maar een massief rood blok met het
  nummer erin, in het raster gezet. Hij komt één keer voor in de hero, één keer als
  smalle vaste strook onderaan op mobiel, en één keer als het nummer zelf, groot gezet,
  in de contactsectie.
- **Het stempel.** Eén gedraaid element op de hele pagina, over de bovenrand van het
  hoofdbeeld heen: `VAN RENSELAAR — AMERSFOORT · SINDS 1990`. Verder staat alles recht.
- **Breedte-as in het zetwerk.** Archivo is een variabel lettertype; koppen staan op
  `font-stretch: 112%` — breder en blokkiger dan de standaardsnede, wat ze een eigen
  stem geeft. Onder 900 px staat de as terug op 100% zodat lange woorden passen.
- **Geen `border-radius` en geen `box-shadow`** in het hele bestand. Geen iconenset,
  geen badges, geen kaarten.

### Kleur

| Rol | Waarde |
|---|---|
| Papier | `#f0ede6` |
| Inkt | `#101418` (diensten, contact, voet) |
| Menie | `#b03a1e` — kantlijn, belbalk, stempel, nummering |
| Grafiet | `#5a6167` — bijschriften en marge |
| Lijn | `#cdc7ba` |

## Foto's toevoegen

Twee beeldplekken, allebei bedoeld voor echte werkfotografie. Zolang er geen foto is,
staat er een vlak met snijtekens in de hoeken — leesbaar als "hier komt beeld".

| Plek | Waar | Verhouding |
|---|---|---|
| Hoofdbeeld onder de hero | `<figure class="band leeg">` | 21:8 desktop, 4:3 mobiel |
| Portret bij blok 01 | `<figure class="portret leeg">` | staand |

Vervangen is één regel — haal `leeg` uit de class weg en zet er een `<img>` in:

```html
<figure class="band"><img src="beeld/werk.jpg" alt="Gert van Renselaar aan het werk"></figure>
```

Kies echte werksituaties: leidingwerk in een kruipruimte, een kraan halverwege de
montage, gereedschap, de servicewagen. Geen stockfoto met opgestoken duim.

## Mobile first

Basisregels gelden voor het kleinste scherm; groter wordt opgebouwd met `min-width`
queries (620 / 700 / 760 / 820 / 880 / 900 / 940 / 1000 / 1100 / 1500 px). De kantlijn
schuift mee: 26 px op een telefoon, 140 px op een breed scherm. Getest van 360 tot
1680 px — geen horizontale overflow, belbalk overal boven de vouw.

## Vóór livegang aanpassen

| Wat | Waar |
|---|---|
| E-mailadres (nu placeholder `info@deloodgieters.nl`) | zoek/vervang |
| Telefoonnummer | `tel:+31641223984` en `06 41 22 39 84` |
| Adres: hier **Gerstekamp 12** — op de huidige contactpagina staat Gerstekamp 17 | zoek op `Gerstekamp` |
| Foto's | zie hierboven |
| Privacyverklaring | link in de voet |
| Bereikbaarheid | contactsectie, "Ma t/m vr, plus spoed" |

Reviewteksten komen van Google; namen zijn overgenomen zoals geplaatst.
