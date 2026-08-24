# deloodgieters.nl — Van Renselaar

Eén statische pagina: `index.html`. Geen build, geen dependencies, geen externe assets
behalve de webfonts. Openen of uploaden en klaar.

## Ontwerprichting

Werkplaats-editorial in plaats van moderne-website-standaard. De structuur komt van
**lijnen, kolommaten en typografie**, niet van kaartjes.

Concreet, en meetbaar in de code:

- **0 keer `border-radius`** — alles is haaks. Geen pillen, geen capsules.
- **0 keer `box-shadow`** — geen zwevende elementen, geen dashboard-look.
- **Geen kaartcomponenten.** Diensten zijn een lijst met haarlijnen, reviews zijn
  losse tekstkolommen, de werkwijze zijn drie genummerde alinea's onder een lijn.
- **Geen iconenset en geen badges.** Vertrouwen zit in de tekst en de compositie:
  het cijfer 4,9 groot gezet, "sinds 1990" in de feitenregel onder de hero,
  plaatsnamen als typografie.
- **Twee lettertypes met een taak.** Archivo voor koppen (strak, zwaar, krappe
  spatiëring), IBM Plex Sans voor lopende tekst, IBM Plex Mono voor labels,
  sectienummers en technische bijschriften.
- **Asymmetrie.** De hero is 1,06 : 0,94 met beeld dat van de rechterrand afloopt;
  "wie u aan de lijn krijgt" is 4,4 : 7,6 met beeld dat links van de rand afloopt;
  reviews zijn 4 : 8. Secties verschillen bewust in hoogte, met een donkere
  rustband tussen de twee drukste secties.

### Kleur

| Rol | Waarde | Gebruik |
|---|---|---|
| Papier | `#efece5` | gebroken wit, warm — de basis |
| Inkt | `#14191c` | hero, rustband, werkgebied, contact, voet |
| Menie | `#b4451f` | de rode loodmenie van leidingwerk: knop, sectienummers, accenten |
| Menie licht | `#e0703f` | accent op donkere vlakken |
| Lijn | `#c9c4b8` | haarlijnen — het belangrijkste structuurmiddel |

### Acties

Eén primaire actie op de hele pagina: **bellen**. Die verschijnt als blokknop in de
hero, als vaste balk onderaan op mobiel, en in de contactsectie als het telefoonnummer
zelf, groot gezet. Mailen is overal een tekstlink met pijl. Verder geen knoppen.

## Foto's toevoegen

Er zijn twee beeldplekken, allebei bedoeld voor echte werkfotografie:

| Plek | Waar in de code | Verhouding |
|---|---|---|
| Hero, rechts | `<figure class="hero__fig">` — vervang het hele `<svg>`-blok | staand tot vierkant |
| Bij "wie u aan de lijn krijgt" | `<figure class="slot who__fig">` — zet er een `<img>` in | staand, ±4:5 |

In beide gevallen volstaat één regel, bijvoorbeeld:

```html
<img src="beeld/gert-van-renselaar.jpg" alt="Gert van Renselaar bij zijn servicewagen">
```

Bijschrift en snijtekens verdwijnen dan vanzelf. Zolang er geen foto is, staat er een
technische tekening (hero) of een beeldvlak met snijtekens (portret) — bewust als
"hier komt beeld", niet als decoratie.

Kies foto's uit echte werksituaties: leidingwerk in een kruipruimte, een kraan halverwege
de montage, gereedschap op een doek, de servicewagen. Geen lachende monteur met opgestoken duim.

## Mobile first

Alle basisregels gelden voor het kleinste scherm; grotere schermen worden opgebouwd met
`min-width` queries (620 / 700 / 820 / 900 / 1000 / 1100 px). Getest van 360 tot 1680 px:
geen horizontale overflow, en de belknop staat op elke breedte boven de vouw.

## Vóór livegang aanpassen

| Wat | Waar |
|---|---|
| E-mailadres (nu placeholder `info@deloodgieters.nl`) | zoek/vervang op `info@deloodgieters.nl` |
| Telefoonnummer | zoek/vervang op `tel:+31641223984` en `06 41 22 39 84` |
| Adres: hier **Gerstekamp 12** — op de huidige contactpagina staat Gerstekamp 17 | zoek op `Gerstekamp` |
| Foto's | zie hierboven |
| Privacyverklaring | link in de voet (`privacyverklaring.html`) |
| Bereikbaarheid | contactsectie, regel "Ma t/m vr, plus spoed" |

Reviewteksten komen van Google; namen zijn overgenomen zoals geplaatst.
