# deloodgieters.nl — Van Renselaar

Eén statische pagina: `index.html`. Geen build, geen frameworks, geen bibliotheken.
De beweging draait op ~90 regels eigen JavaScript en CSS-transities.

## Vorm

**De kantlijn.** Eén rode lijn loopt van de eerste tot de laatste pixel van de pagina en
**vult zich mee met hoever je gelezen hebt**. Identiteit en voortgangsmeter in hetzelfde
element. Alle inhoud hangt aan die lijn; in de marge ernaast staat alleen de nummering
van de blokken.

**Papier, inkt, menie.** Gebroken wit `#f0ede6`, dieptezwart `#101418`, meniërood
`#c8441f` — de rode grondverf van leidingwerk. Geen slagschaduwen, geen iconenset,
geen badges, geen kaarten.

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
| Lijnen | tekenen zich van links naar rechts, met vertraging per element |
| Werkgebied | de cirkels van de kaart zetten zich uit bij binnenkomst |
| Belbalk | vult zich van onderen met inkt bij hover |

Alles is uitgeschakeld bij `prefers-reduced-motion: reduce`: geen parallax, geen
doorlopende namen, geen maskers, cijfers meteen op eindwaarde. De pagina blijft dan
volledig leesbaar en compleet.

## Beeld — LET OP vóór livegang

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
