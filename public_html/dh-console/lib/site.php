<?php
/**
 * Alles wat op ELKE pagina hetzelfde hoort te zijn:
 *   - de kop (header) en de voet (footer), uit sjablonen/kop.html en voet.html
 *   - /sitemap.xml
 *
 * Waarom hier en niet met de hand: kop en voet stonden in zeventien pagina's
 * afzonderlijk. Dat liep uiteen -- de ene voet zei "Cookies" en de andere
 * "Cookiebeleid", en op /contact stond per ongeluk aria-current="page" op
 * Web Design & Development. Eén sjabloon, één keer stempelen, klaar.
 *
 * De bezoeker krijgt nog steeds geen PHP te zien: dit schrijft gewone HTML.
 */

require_once __DIR__ . '/hulp.php';
require_once __DIR__ . '/bouw.php';   // voor vervang_tussen()

/**
 * De vaste pagina's van de site.
 *
 *   pad      bestand onder SITE_MAP
 *   url      adres zonder domein; ook waar de kop aria-current op zet
 *   wijzigt  changefreq voor de sitemap
 *   gewicht  priority voor de sitemap
 *
 * Cases en artikelen staan hier niet: die komen uit cases.json en
 * artikelen.json en worden er in bouw_sitemap() bij gezocht.
 */
function vaste_paginas(): array
{
    return [
        ['pad' => 'index.html',                                 'url' => '/',                                'wijzigt' => 'weekly',  'gewicht' => '1.0'],
        ['pad' => 'diensten/index.html',                        'url' => '/diensten',                        'wijzigt' => 'monthly', 'gewicht' => '0.9'],
        ['pad' => 'diensten/web-design-development/index.html', 'url' => '/diensten/web-design-development',  'wijzigt' => 'monthly', 'gewicht' => '0.9'],
        ['pad' => 'diensten/seo/index.html',                    'url' => '/diensten/seo',                    'wijzigt' => 'monthly', 'gewicht' => '0.9'],
        ['pad' => 'diensten/hosting-onderhoud/index.html',      'url' => '/diensten/hosting-onderhoud',      'wijzigt' => 'monthly', 'gewicht' => '0.9'],
        ['pad' => 'tarieven/index.html',                        'url' => '/tarieven',                        'wijzigt' => 'monthly', 'gewicht' => '0.9'],
        ['pad' => 'portfolio/index.html',                       'url' => '/portfolio',                       'wijzigt' => 'weekly',  'gewicht' => '0.8'],
        ['pad' => 'blog/index.html',                            'url' => '/blog',                            'wijzigt' => 'weekly',  'gewicht' => '0.8'],
        ['pad' => 'over-dh-studio/index.html',                  'url' => '/over-dh-studio',                  'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'website-concept/index.html',                 'url' => '/website-concept',                 'wijzigt' => 'monthly', 'gewicht' => '0.8'],
        ['pad' => 'werkgebied/index.html',                     'url' => '/werkgebied',                      'wijzigt' => 'yearly',  'gewicht' => '0.6'],
        ['pad' => 'website-laten-maken-nijkerk/index.html',     'url' => '/website-laten-maken-nijkerk',     'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'website-laten-maken-amersfoort/index.html',  'url' => '/website-laten-maken-amersfoort',  'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'website-laten-maken-putten/index.html',      'url' => '/website-laten-maken-putten',      'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'website-laten-maken-ermelo/index.html',      'url' => '/website-laten-maken-ermelo',      'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'website-laten-maken-harderwijk/index.html',  'url' => '/website-laten-maken-harderwijk',  'wijzigt' => 'monthly', 'gewicht' => '0.7'],
        ['pad' => 'contact/index.html',                         'url' => '/contact',                         'wijzigt' => 'monthly', 'gewicht' => '0.8'],
        ['pad' => 'privacybeleid/index.html',                   'url' => '/privacybeleid',                   'wijzigt' => 'yearly',  'gewicht' => '0.3'],
        ['pad' => 'voorwaarden/index.html',                     'url' => '/voorwaarden',                     'wijzigt' => 'yearly',  'gewicht' => '0.3'],
        ['pad' => 'cookies/index.html',                         'url' => '/cookies',                         'wijzigt' => 'yearly',  'gewicht' => '0.3'],

        // Wel een kop en voet, niet in de sitemap: staat op noindex.
        ['pad' => '404.html',                                   'url' => null,                               'wijzigt' => null,      'gewicht' => null],
    ];
}

/* ============================================================ kop en voet */

/**
 * Zet de kop en de voet opnieuw in alle pagina's, en in de sjablonen waar
 * cases en artikelen uit gebouwd worden.
 */
function bouw_koppen_voeten(): array
{
    $kop = sjabloon_zonder_witregel('kop.html');
    $voet = sjabloon_zonder_witregel('voet.html');

    $meldingen = [];
    $aantal = 0;

    foreach (vaste_paginas() as $pagina) {
        $pad = SITE_MAP . '/' . $pagina['pad'];
        if (!is_file($pad)) {
            $meldingen[] = 'Overgeslagen: /' . $pagina['pad'] . ' bestaat niet.';
            continue;
        }
        stempel_kop_en_voet($pad, $kop, $voet, $pagina['url']);
        $aantal++;
    }
    $meldingen[] = 'Kop en voet bijgewerkt op ' . $aantal . ' ' . ($aantal === 1 ? 'pagina' : "pagina's") . '.';

    // Een case staat onder Portfolio in het menu, een artikel onder Blog.
    foreach (['case.html' => '/portfolio', 'artikel.html' => '/blog'] as $bestand => $url) {
        $pad = SJABLOON_MAP . '/' . $bestand;
        if (is_file($pad)) {
            stempel_kop_en_voet($pad, $kop, $voet, $url);
            $meldingen[] = 'Sjabloon ' . $bestand . ' bijgewerkt.';
        }
    }

    // En de pagina's die al uit die sjablonen gebouwd zijn, want anders
    // lopen die achter tot iemand toevallig opnieuw publiceert.
    foreach ([PORTFOLIO_MAP => '/portfolio', BLOG_MAP => '/blog'] as $map => $url) {
        $los = 0;
        foreach (glob($map . '/*/index.html') ?: [] as $pad) {
            stempel_kop_en_voet($pad, $kop, $voet, $url);
            $los++;
        }
        if ($los > 0) {
            $meldingen[] = $los . ' ' . ($los === 1 ? 'pagina' : "pagina's") . ' onder ' . $url . ' bijgewerkt.';
        }
    }
    return $meldingen;
}

/** Leest een sjabloon en haalt de afsluitende regelovergang eraf. */
function sjabloon_zonder_witregel(string $bestand): string
{
    $pad = SJABLOON_MAP . '/' . $bestand;
    if (!is_file($pad)) {
        throw new RuntimeException('Het sjabloon ' . $bestand . ' ontbreekt in ' . SJABLOON_MAP . '.');
    }
    return rtrim((string) file_get_contents($pad), "\r\n");
}

/** Schrijft kop en voet in één pagina. */
function stempel_kop_en_voet(string $pad, string $kop, string $voet, ?string $url): void
{
    $waar = str_replace(SITE_MAP . '/', '', $pad);
    $html = (string) file_get_contents($pad);
    $html = vervang_tussen($html, 'KOP', markeer_actief($kop, $url), $waar);
    $html = vervang_tussen($html, 'VOET', markeer_actief($voet, $url), $waar);
    schrijf_bestand($pad, $html);
}

/**
 * Zet de "u bent hier"-aanduiding in het menu.
 *
 * Regels, en meer zijn het er niet:
 *   - elke <a href="…"> die precies naar deze pagina wijst, krijgt
 *     aria-current="page";
 *   - zit de pagina onder /diensten, dan klapt het Diensten-menu open
 *     (is-actief op de knop, open op de mobiele <details>).
 *
 * Doordat dit uit de URL volgt, kan het niet meer uit de pas lopen met de
 * pagina waar het op staat.
 */
function markeer_actief(string $html, ?string $url): string
{
    if ($url === null) {
        return $html;
    }

    // aria-current op de links naar deze pagina zelf. De vervanging pakt
    // alleen href="<url>" precies -- niet /diensten binnen /diensten/seo.
    $html = preg_replace_callback(
        '/<a\s([^>]*\bhref="' . preg_quote($url, '/') . '")([^>]*)>/',
        static fn(array $m): string => '<a ' . $m[1] . ' aria-current="page"' . $m[2] . '>',
        $html
    ) ?? $html;

    if (str_starts_with($url, '/diensten')) {
        $html = str_replace(
            ['class="nav-drop-trigger"', '<details class="mobile-sub">'],
            ['class="nav-drop-trigger is-actief"', '<details class="mobile-sub" open>'],
            $html
        );
    }

    // Op de homepage staat het dienstenblok op de pagina zelf. In het mobiele
    // menu is <summary> geen link, dus zonder deze regel kan een bezoeker op
    // een telefoon niet naar dat blok springen. Alleen daar, want elders zou
    // "op deze pagina" niet kloppen.
    if ($url === '/') {
        $html = str_replace(
            "\t\t\t\t\t\t<a href=\"/diensten/web-design-development\">",
            "\t\t\t\t\t\t<a href=\"#diensten\">Diensten op deze pagina</a>\n"
                . "\t\t\t\t\t\t<a href=\"/diensten/web-design-development\">",
            $html
        );
    }
    return $html;
}

/* ============================================================== sitemap */

/**
 * Schrijft /sitemap.xml: de vaste pagina's uit vaste_paginas(), plus elke
 * zichtbare case en elk zichtbaar artikel.
 *
 * /404.html blijft eruit (noindex), en /blog blijft eruit zolang er geen
 * artikelen zijn -- bouw_blogoverzicht() zet die pagina dan zelf op noindex,
 * en een noindex-pagina in je sitemap zetten is een tegenstrijdig signaal.
 */
function bouw_sitemap(array $cases = [], array $artikelen = []): array
{
    $zichtbaar = static fn(array $rijen): array => array_values(
        array_filter($rijen, static fn(array $r): bool => !empty($r['zichtbaar']))
    );
    $cases = $zichtbaar($cases);
    $artikelen = $zichtbaar($artikelen);

    $regels = [];
    foreach (vaste_paginas() as $pagina) {
        if ($pagina['url'] === null) {
            continue;
        }
        if ($pagina['url'] === '/blog' && $artikelen === []) {
            continue;
        }
        $regels[] = sitemap_regel(
            $pagina['url'],
            gewijzigd_op(SITE_MAP . '/' . $pagina['pad']),
            $pagina['wijzigt'],
            $pagina['gewicht']
        );
    }

    foreach ($cases as $case) {
        $regels[] = sitemap_regel(
            '/portfolio/' . $case['slug'],
            gewijzigd_op(PORTFOLIO_MAP . '/' . $case['slug'] . '/index.html'),
            'yearly',
            '0.6'
        );
    }

    foreach ($artikelen as $artikel) {
        // Voor een artikel is de publicatiedatum eerlijker dan de
        // bestandsdatum: die verspringt bij elke herbouw.
        $datum = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($artikel['datum'] ?? ''))
            ? $artikel['datum']
            : gewijzigd_op(BLOG_MAP . '/' . $artikel['slug'] . '/index.html');
        $regels[] = sitemap_regel('/blog/' . $artikel['slug'], $datum, 'yearly', '0.6');
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
        . implode('', $regels)
        . '</urlset>' . "\n";

    schrijf_bestand(SITE_MAP . '/sitemap.xml', $xml);

    return ['Sitemap geschreven (' . count($regels) . ' ' . (count($regels) === 1 ? 'adres' : 'adressen') . ').'];
}

function sitemap_regel(string $url, string $datum, string $wijzigt, string $gewicht): string
{
    return "\t<url>\n"
        . "\t\t<loc>" . esc(BASIS_URL . $url) . "</loc>\n"
        . "\t\t<lastmod>" . esc($datum) . "</lastmod>\n"
        . "\t\t<changefreq>" . esc($wijzigt) . "</changefreq>\n"
        . "\t\t<priority>" . esc($gewicht) . "</priority>\n"
        . "\t</url>\n";
}

/** Datum van laatste wijziging als JJJJ-MM-DD; vandaag als het bestand weg is. */
function gewijzigd_op(string $pad): string
{
    $tijd = is_file($pad) ? filemtime($pad) : false;
    return date('Y-m-d', $tijd === false ? time() : $tijd);
}

/* ================================================================ alles */

/** Kop, voet en sitemap in één keer. Dit draait na elke publicatie. */
function bouw_site(array $cases = [], array $artikelen = []): array
{
    return array_merge(
        bouw_koppen_voeten(),
        bouw_sitemap($cases, $artikelen)
    );
}
