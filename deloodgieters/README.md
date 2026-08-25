# deloodgieters.nl — Van Renselaar

Eén statische pagina: `index.html`. Geen build, geen frameworks, geen bibliotheken.
De beweging draait op ~90 regels eigen JavaScript en CSS-transities.

## Vorm

**De kantlijn.** Eén rode lijn loopt van de eerste tot de laatste pixel van de pagina en
**vult zich mee met hoever je gelezen hebt**. Identiteit en voortgangsmeter in hetzelfde
element. Alle inhoud hangt aan die lijn; in de marge ernaast staat alleen de nummering
van de blokken.

**Vier kleuren met elk een taak.** Papier `#f2efe7` en inkt `#0e1519` (met een
petrolzweem) dragen de pagina. Daarbovenop:

| Kleur | Waar |
|---|---|
| Menie `#c8441f` | **bellen** — de kantlijn, de belbalk, de nummering, de cirkels op de kaart |
| Water `#2f6f88` | **mailen** — de e-maillinks, de stappen 01/02/03, de Eem op de kaart |
| Goud `#e0a33a` | de sterren van de beoordeling |
| Zand `#e9e1d1` | het getinte paneel van het werkgebied |

Bellen is warm en dringend, mailen koel en bedachtzaam: de twee acties zijn overal aan
hun kleur te herkennen. De hero heeft een verloop met een koele kant links en een warme
gloed rechtsonder.

Verder geen slagschaduwen, geen iconenset, geen badges, geen kaarten.

**Panelen in plaats van banen.** De donkere en getinte vlakken lopen niet meer van rand
tot rand: ze liggen als afgeronde panelen op het papier, met het papier zichtbaar
eromheen. De hero krult onderaan weg, contact en voet vormen samen één paneel dat de
pagina afsluit. Dat haalt de stapel rechthoeken uit de compositie.

**Maatvoering van de ronding.** Vier waarden, verder niets: `--rond: 4px` voor knoppen,
`--rond-b: 14px` voor beeld, `--rondp: 18–30px` voor panelen en `--inzet: 10–22px` voor
hoeveel een paneel van de schermrand blijft. De kantlijn en de accentstrepen hebben ronde
uiteinden.

**De kaart.** Het werkgebied is een schematische kaart: de gemeentecontour als organische
vorm, twee cirkels voor de straal, de Eem en de doorgaande wegen als gebogen lijnen
binnen de contour, en vijf wijken benoemd. De cirkels zetten zich uit zodra de kaart in
beeld komt. Alle wijknamen staan als tekst in de kolom ernaast, dus de informatie hangt
niet aan het beeld.

**Zetwerk.** Archivo op de variabele breedte-as (112% op desktop, 100% daaronder zodat
lange woorden passen), IBM Plex Sans voor lopende tekst, IBM Plex Mono voor de marge.

## Beweging

| Waar | Wat |
|---|---|
| Kantlijn | vult van boven naar beneden met de scrollpositie |
| Hero | koptekst komt regel voor regel onder een masker omhoog; foto beweegt trager dan de pagina (parallax) |
| Kopbalk | transparant over de hero, wordt papier zodra je eronder bent |
| Belbalk mobiel | schuift pas omhoog ná de hero |
| Werk | de foto **blijft staan** terwijl de dienstenlijst erlangs schuift en wisselt per dienst |
| Cijfers | 35 jaar, 92 reviews, 90%, 4,9 lopen op zodra ze in beeld komen |
| Sterren | 4,9 van 5 staat als sterren in de hero én bij het grote cijfer; de vijfde ster is voor 90% gevuld |
| Lijnen | tekenen zich van links naar rechts, met vertraging per element |
| Werkgebied | de cirkels van de kaart zetten zich uit bij binnenkomst |
| Belbalk | vult zich van onderen met inkt bij hover |

Alles is uitgeschakeld bij `prefers-reduced-motion: reduce`: geen parallax, geen
doorlopende namen, geen maskers, cijfers meteen op eindwaarde. De pagina blijft dan
volledig leesbaar en compleet.

## Eigen beeld

Twee plekken wachten op bestanden uit `beeld/` (zie `beeld/LEESMIJ.md`):

- **`beeld/van-renselaar-bus.jpg`** — de foto van Gert naast de bus, in blok 01. De
  markup staat er al; zodra het bestand er staat verschijnt hij.
- **`beeld/logo.svg`** — het orka-merk van de bus. De site heeft nu geen merkteken; de
  regel staat als opmerking klaar in het `<a class="merk">`-blok in de kopbalk. Een
  natekening op basis van de foto was niet goed genoeg om te plaatsen, dus dit vraagt om
  het echte logobestand.

## Gegenereerd beeld — LET OP vóór livegang

De acht foto's zijn **met AI gegenereerd** (Higgsfield, nano-banana) als tussenstand, en
staan nu nog op de CDN van Higgsfield. Twee dingen zijn nodig:

1. **Zelf hosten.** Download de bestanden en zet ze in `beeld/`; pas daarna de `src`'en
   aan. Een externe CDN-link is niets waard voor een productiesite.
2. **Bekijken en vervangen.** Ik kon ze in deze omgeving niet zelf bekijken (de
   netwerkpolicy blokkeert die host), dus controleer ze met eigen ogen — AI-beeld van
   handen en gereedschap gaat regelmatig mis. Echte foto's van Gert aan het werk zijn
   hoe dan ook beter: dat is precies het verschil tussen een site die klopt en een site
   die aanvoelt als voorraadbeeld.

Volgorde in de code: hero (koppeling), 6 diensten (kruipruimte, gereedschap, sanitair,
solderen, radiator, verdeler) en de achtergrond van het werkgebied (straat).

## Mobile first

Basisregels gelden voor het kleinste scherm; groter wordt opgebouwd met `min-width`
queries (620 / 700 / 760 / 820 / 900 / 940 / 1000 / 1100 / 1500 px). De kantlijn schuift
mee: 24 px op een telefoon, 136 px op een breed scherm. Het meelopende beeld bij Werk is
desktop-only; op een telefoon staat de foto gewoon bij de dienst. Getest van 360 tot
1680 px, geen horizontale overflow.

## Vóór livegang aanpassen

| Wat | Waar |
|---|---|
| Foto's | zie hierboven |
| E-mailadres (nu placeholder `info@deloodgieters.nl`) | zoek/vervang |
| Telefoonnummer | `tel:+31641223984` en `06 41 22 39 84` |
| Adres: hier **Gerstekamp 12** — op de huidige contactpagina staat Gerstekamp 17 | zoek op `Gerstekamp` |
| Privacyverklaring | link in de voet |
| Bereikbaarheid | contactsectie, "Ma t/m vr, plus spoed" |

Reviewteksten komen van Google; namen zijn overgenomen zoals geplaatst.
