<?php
/**
 * Zet de tekst uit het beheerformulier om naar de HTML van een casepagina.
 *
 * De opmaak is met opzet klein gehouden:
 *
 *   # Kop            begint een nieuw hoofdstuk (krijgt een nummer en komt in de inhoudsopgave)
 *   ## Kop           begint een keuze-blok binnen een hoofdstuk
 *   > tekst          een uitspraak, dik gezet met een streep ervoor
 *   - **Vet.** rest  een opsomming van dingen die je niet doet
 *   = 42 kB | label  een cijfer in het blok met kerngetallen
 *   lege regel       scheidt alinea's
 */

/** Leest de bron en geeft een lijst hoofdstukken terug. */
function lees_artikel(string $bron): array
{
    $regels = preg_split('/\R/u', str_replace("\r\n", "\n", $bron));
    $hoofdstukken = [];
    $huidig = null;
    $buffer = [];
    $soort = 'tekst';

    $leeg_buffer = static function () use (&$buffer, &$soort, &$huidig): void {
        if ($huidig === null || $buffer === []) {
            $buffer = [];
            return;
        }
        if ($soort === 'keuze') {
            $kop = array_shift($buffer);
            $huidig['blokken'][] = ['soort' => 'keuze', 'kop' => $kop, 'tekst' => implode(' ', $buffer)];
        } elseif ($soort === 'lijst') {
            $huidig['blokken'][] = ['soort' => 'lijst', 'items' => $buffer];
        } elseif ($soort === 'cijfers') {
            $huidig['blokken'][] = ['soort' => 'cijfers', 'items' => $buffer];
        } elseif ($soort === 'quote') {
            $huidig['blokken'][] = ['soort' => 'quote', 'tekst' => implode(' ', $buffer)];
        } else {
            $huidig['blokken'][] = ['soort' => 'tekst', 'tekst' => implode(' ', $buffer)];
        }
        $buffer = [];
        $soort = 'tekst';
    };

    foreach ($regels as $regel) {
        $regel = rtrim($regel);
        $kaal = trim($regel);

        if ($kaal === '') {
            $leeg_buffer();
            continue;
        }
        if (str_starts_with($kaal, '# ')) {
            $leeg_buffer();
            if ($huidig !== null) {
                $hoofdstukken[] = $huidig;
            }
            $huidig = ['kop' => trim(substr($kaal, 2)), 'blokken' => []];
            continue;
        }
        if ($huidig === null) {
            // tekst voor het eerste hoofdstuk hoort nergens bij; negeren
            continue;
        }
        if (str_starts_with($kaal, '## ')) {
            $leeg_buffer();
            $soort = 'keuze';
            $buffer[] = trim(substr($kaal, 3));
            continue;
        }
        if (str_starts_with($kaal, '> ')) {
            if ($soort !== 'quote') {
                $leeg_buffer();
            }
            $soort = 'quote';
            $buffer[] = trim(substr($kaal, 2));
            continue;
        }
        if (str_starts_with($kaal, '- ')) {
            if ($soort !== 'lijst') {
                $leeg_buffer();
            }
            $soort = 'lijst';
            $buffer[] = trim(substr($kaal, 2));
            continue;
        }
        if (str_starts_with($kaal, '= ')) {
            if ($soort !== 'cijfers') {
                $leeg_buffer();
            }
            $soort = 'cijfers';
            $buffer[] = trim(substr($kaal, 2));
            continue;
        }
        // gewone regel: hoort bij wat er boven staat
        $buffer[] = $kaal;
    }
    $leeg_buffer();
    if ($huidig !== null) {
        $hoofdstukken[] = $huidig;
    }
    return $hoofdstukken;
}

/** De inhoudsopgave in de kantlijn. */
function artikel_inhoudsopgave(array $hoofdstukken, string $tab): string
{
    $uit = '';
    foreach ($hoofdstukken as $i => $hfd) {
        $id = 'hfd-' . ($i + 1);
        $uit .= $tab . '<li><a href="#' . $id . '">' . esc($hfd['kop']) . "</a></li>\n";
    }
    return rtrim($uit, "\n");
}

/** De hoofdstukken zelf. */
function artikel_naar_html(array $hoofdstukken, string $tab): string
{
    $uit = '';
    foreach ($hoofdstukken as $i => $hfd) {
        $id = 'hfd-' . ($i + 1);
        $nr = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
        $uit .= $tab . '<section class="hfd" id="' . $id . '">' . "\n";
        $uit .= $tab . "\t" . '<span class="hfd-nr">' . $nr . '</span>' . "\n";
        $uit .= $tab . "\t" . '<h2>' . esc($hfd['kop']) . '</h2>' . "\n";

        $keuzes = [];
        foreach ($hfd['blokken'] as $blok) {
            switch ($blok['soort']) {
                case 'tekst':
                    $uit .= $tab . "\t" . '<p>' . tekst_naar_html($blok['tekst']) . '</p>' . "\n";
                    break;
                case 'quote':
                    $uit .= $tab . "\t" . '<blockquote class="uitspraak">' . tekst_naar_html($blok['tekst']) . '</blockquote>' . "\n";
                    break;
                case 'keuze':
                    $uit .= $tab . "\t" . '<div class="keuze">' . "\n";
                    $uit .= $tab . "\t\t" . '<h3>' . esc($blok['kop']) . '</h3>' . "\n";
                    $uit .= $tab . "\t\t" . '<p>' . tekst_naar_html($blok['tekst']) . '</p>' . "\n";
                    $uit .= $tab . "\t" . '</div>' . "\n";
                    break;
                case 'lijst':
                    $uit .= $tab . "\t" . '<ul class="geen">' . "\n";
                    foreach ($blok['items'] as $item) {
                        $uit .= $tab . "\t\t" . '<li><span>' . tekst_naar_html($item) . '</span></li>' . "\n";
                    }
                    $uit .= $tab . "\t" . '</ul>' . "\n";
                    break;
                case 'cijfers':
                    $uit .= $tab . "\t" . '<div class="cijfers">' . "\n";
                    foreach ($blok['items'] as $item) {
                        [$getal, $label] = array_pad(array_map('trim', explode('|', $item, 2)), 2, '');
                        $uit .= $tab . "\t\t" . '<div><b>' . esc($getal) . '</b><span>' . esc($label) . '</span></div>' . "\n";
                    }
                    $uit .= $tab . "\t" . '</div>' . "\n";
                    break;
            }
        }
        unset($keuzes);
        $uit .= $tab . '</section>' . "\n\n";
    }
    return rtrim($uit, "\n");
}

/**
 * Zelfde bron-opmaak, maar voor een blogartikel: geen genummerde
 * hoofdstukken en geen inhoudsopgave, gewoon koppen en alinea's.
 */
function blog_naar_html(array $hoofdstukken, string $tab): string
{
    $uit = '';
    foreach ($hoofdstukken as $hfd) {
        if ($hfd['kop'] !== '') {
            $uit .= $tab . '<h2>' . esc($hfd['kop']) . '</h2>' . "\n";
        }
        foreach ($hfd['blokken'] as $blok) {
            switch ($blok['soort']) {
                case 'tekst':
                    $uit .= $tab . '<p>' . tekst_naar_html($blok['tekst']) . '</p>' . "\n";
                    break;
                case 'quote':
                    $uit .= $tab . '<blockquote>' . tekst_naar_html($blok['tekst']) . '</blockquote>' . "\n";
                    break;
                case 'keuze':
                    $uit .= $tab . '<h3>' . esc($blok['kop']) . '</h3>' . "\n";
                    $uit .= $tab . '<p>' . tekst_naar_html($blok['tekst']) . '</p>' . "\n";
                    break;
                case 'lijst':
                    $uit .= $tab . '<ul>' . "\n";
                    foreach ($blok['items'] as $item) {
                        $uit .= $tab . "\t" . '<li><span>' . tekst_naar_html($item) . '</span></li>' . "\n";
                    }
                    $uit .= $tab . '</ul>' . "\n";
                    break;
                case 'cijfers':
                    $uit .= $tab . '<div class="cijfers">' . "\n";
                    foreach ($blok['items'] as $item) {
                        [$getal, $label] = array_pad(array_map('trim', explode('|', $item, 2)), 2, '');
                        $uit .= $tab . "\t" . '<div><b>' . esc($getal) . '</b><span>' . esc($label) . '</span></div>' . "\n";
                    }
                    $uit .= $tab . '</div>' . "\n";
                    break;
            }
        }
    }
    return rtrim($uit, "\n");
}
