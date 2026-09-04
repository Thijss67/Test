<?php
/**
 * Zet de gegevens uit data/cases.json om naar gewone HTML-bestanden:
 *   - /portfolio/index.html          (de kaarten tussen de CASES-markeringen)
 *   - /portfolio/<slug>/index.html   (de casepagina zelf, uit sjablonen/case.html)
 *
 * De bezoeker krijgt dus nooit PHP te zien; de site blijft statisch en snel.
 */

require_once __DIR__ . '/artikel.php';

/** Bouwt alles opnieuw op. Geeft een lijst met meldingen terug. */
function bouw_alles(array $cases): array
{
    $meldingen = [];
    $zichtbaar = array_values(array_filter($cases, static fn(array $c): bool => !empty($c['zichtbaar'])));

    bouw_overzicht($zichtbaar);
    $meldingen[] = 'Portfolio-overzicht bijgewerkt (' . count($zichtbaar) . ' ' . (count($zichtbaar) === 1 ? 'case' : 'cases') . ').';

    foreach ($zichtbaar as $case) {
        bouw_casepagina($case);
        $meldingen[] = 'Casepagina /portfolio/' . $case['slug'] . ' geschreven.';
    }

    // pagina's van cases die niet meer zichtbaar zijn, weghalen
    $slugs = array_column($zichtbaar, 'slug');
    foreach (glob(PORTFOLIO_MAP . '/*', GLOB_ONLYDIR) ?: [] as $map) {
        $naam = basename($map);
        if (!in_array($naam, $slugs, true) && geldige_slug($naam)) {
            @unlink($map . '/index.html');
            @rmdir($map);
            $meldingen[] = 'Oude pagina /portfolio/' . $naam . ' verwijderd.';
        }
    }
    return $meldingen;
}

/** De kaarten op /portfolio. */
function bouw_overzicht(array $cases): void
{
    $pad = PORTFOLIO_MAP . '/index.html';
    $html = (string) file_get_contents($pad);

    $kaarten = '';
    foreach ($cases as $case) {
        $labels = '';
        foreach ($case['labels'] as $label) {
            $labels .= "\t\t\t\t\t\t\t\t\t" . '<span>' . esc($label) . '</span>' . "\n";
        }
        $kaarten .= <<<HTML
						<a class="case-kaart reveal" href="/portfolio/{$case['slug']}" data-cta="case-{$case['slug']}">
							<span class="kaart-beeld">
								<img src="{$case['afbeelding']}" width="1400" height="875" loading="lazy" decoding="async" alt="{$case['altEsc']}" />
							</span>
							<span class="kaart-body">
								<span class="kaart-titel">{$case['naamEsc']}</span>
								<span class="kaart-labels">
{$labels}								</span>
								<span class="kaart-tekst">
									{$case['kaartHtml']}
								</span>
								<span class="kaart-lees">
									Bekijk case
									<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
								</span>
							</span>
						</a>

HTML;
    }
    if ($kaarten === '') {
        $kaarten = "\t\t\t\t\t\t" . '<p class="kaart-tekst">Binnenkort staan hier onze eerste cases.</p>' . "\n";
    }

    $html = vervang_tussen($html, 'CASES', rtrim($kaarten, "\n"));
    $html = vervang_tussen($html, 'SCHEMA', schema_overzicht($cases));
    schrijf_bestand($pad, $html);
}

/** Een enkele casepagina. */
function bouw_casepagina(array $case): void
{
    $sjabloon = (string) file_get_contents(SJABLOON_MAP . '/case.html');
    $url = BASIS_URL . '/portfolio/' . $case['slug'];
    $titel = $case['titelEsc'] . ' | DH Studio';

    $inhoud = case_inhoud($case);
    $vervang = [
        '{{TITEL}}'        => $titel,
        '{{OMSCHRIJVING}}' => $case['omschrijvingEsc'],
        '{{URL}}'          => $url,
        '{{BEELD}}'        => BASIS_URL . $case['afbeelding'],
        '{{INHOUD}}'       => $inhoud,
        '{{SCHEMA}}'       => schema_case($case, $url),
    ];
    $html = strtr($sjabloon, $vervang);
    schrijf_bestand(PORTFOLIO_MAP . '/' . $case['slug'] . '/index.html', $html);
}

/** De hoofdinhoud van een casepagina. */
function case_inhoud(array $case): string
{
    $pijl = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';

    $feiten = '';
    foreach ($case['feiten'] as $feit) {
        $feiten .= "\t\t\t\t\t\t" . '<div><dt>' . esc($feit['label']) . '</dt><dd>' . tekst_naar_html($feit['waarde']) . '</dd></div>' . "\n";
    }

    $hoofdstukken = lees_artikel($case['artikel']);
    $inhoudsopgave = artikel_inhoudsopgave($hoofdstukken, "\t\t\t\t\t\t\t");
    $artikel = artikel_naar_html($hoofdstukken, "\t\t\t\t\t\t");

    $nummer = str_pad((string) $case['nummer'], 2, '0', STR_PAD_LEFT);

    return <<<HTML
			<nav class="wrap kruimels" aria-label="Kruimelpad">
				<ol>
					<li><a href="/">Home</a></li>
					<li>{$pijl}</li>
					<li><a href="/portfolio">Portfolio</a></li>
					<li>{$pijl}</li>
					<li aria-current="page">{$case['naamEsc']}</li>
				</ol>
			</nav>

			<section class="section case-kop">
				<div class="wrap">
					<span class="eyebrow">Case {$nummer} &middot; {$case['jaarEsc']}</span>
					<h1>{$case['titelEsc']}</h1>
					<p class="lead">
						{$case['leadHtml']}
					</p>

					<dl class="feiten">
{$feiten}					</dl>

					<figure class="case-beeld reveal">
						<img src="{$case['afbeelding']}" width="1400" height="875" decoding="async" alt="{$case['altEsc']}" />
						<figcaption>{$case['bijschriftEsc']}</figcaption>
					</figure>
				</div>
			</section>

			<section class="section" style="padding-top: 0">
				<div class="wrap case-body">
					<aside class="case-inhoud">
						<p class="inhoud-kop">Inhoud</p>
						<ol class="inhoud">
{$inhoudsopgave}
						</ol>
					</aside>

					<div class="artikel">
{$artikel}

						<p class="terug"><a href="/portfolio">&larr; Terug naar het portfolio</a></p>
					</div>
				</div>
			</section>

			<section class="section section-soft">
				<div class="wrap slot reveal">
					<h2>Zo pakken we jouw site ook aan</h2>
					<p>
						Vertel kort wat je bedrijf doet en wat er beter moet. Wij laten vrijblijvend zien
						wat er mogelijk is &mdash; met dezelfde keuzes en dezelfde uitleg erbij.
					</p>
					<div class="btn-rij">
						<a href="/contact" class="btn btn-primary" data-cta="case-contact">Gratis kennismaken <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
						<a href="/website-concept" class="btn btn-outline" data-cta="case-concept">Gratis websiteconcept</a>
					</div>
				</div>
			</section>
HTML;
}

/** Vervangt alles tussen <!-- NAAM:START --> en <!-- NAAM:EIND -->. */
function vervang_tussen(string $html, string $naam, string $nieuw): string
{
    $start = '<!-- ' . $naam . ':START -->';
    $eind = '<!-- ' . $naam . ':EIND -->';
    $a = strpos($html, $start);
    $b = strpos($html, $eind);
    if ($a === false || $b === false || $b < $a) {
        throw new RuntimeException('De markering ' . $naam . ' ontbreekt in portfolio/index.html.');
    }
    return substr($html, 0, $a + strlen($start)) . "\n" . $nieuw . "\n" . substr($html, $b);
}

function schema_overzicht(array $cases): string
{
    $lijst = [];
    foreach ($cases as $i => $case) {
        $lijst[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => BASIS_URL . '/portfolio/' . $case['slug'],
            'item' => [
                '@type' => 'CreativeWork',
                'name' => $case['naam'],
                'description' => $case['omschrijving'],
                'dateCreated' => $case['jaar'],
                'creator' => ['@id' => BASIS_URL . '/#organisatie'],
            ],
        ];
    }
    $graaf = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'CollectionPage',
                '@id' => BASIS_URL . '/portfolio#webpage',
                'url' => BASIS_URL . '/portfolio',
                'name' => 'Portfolio',
                'description' => 'Websites die DH Studio heeft ontworpen en gebouwd, met de keuzes erachter uitgeschreven.',
                'inLanguage' => 'nl-NL',
                'isPartOf' => ['@id' => BASIS_URL . '/#website'],
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
                    'itemListElement' => $lijst,
                ],
            ],
            kruimels_schema([
                ['Home', BASIS_URL . '/'],
                ['Portfolio', BASIS_URL . '/portfolio'],
            ], BASIS_URL . '/portfolio#kruimelpad'),
        ],
    ];
    return '<script type="application/ld+json">' . "\n" . json_uit($graaf) . "\n\t\t</script>";
}

function schema_case(array $case, string $url): string
{
    $graaf = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                '@id' => $url . '#case',
                'url' => $url,
                'headline' => 'Case: ' . $case['titel'],
                'description' => $case['omschrijving'],
                'inLanguage' => 'nl-NL',
                'image' => BASIS_URL . $case['afbeelding'],
                'author' => ['@id' => BASIS_URL . '/#organisatie'],
                'publisher' => ['@id' => BASIS_URL . '/#organisatie'],
                'about' => ['@type' => 'CreativeWork', 'name' => $case['naam']],
                'isPartOf' => ['@id' => BASIS_URL . '/#website'],
            ],
            kruimels_schema([
                ['Home', BASIS_URL . '/'],
                ['Portfolio', BASIS_URL . '/portfolio'],
                [$case['naam'], $url],
            ], $url . '#kruimelpad'),
        ],
    ];
    return json_uit($graaf);
}

function kruimels_schema(array $stappen, string $id): array
{
    $items = [];
    foreach ($stappen as $i => [$naam, $adres]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $naam, 'item' => $adres];
    }
    return ['@type' => 'BreadcrumbList', '@id' => $id, 'itemListElement' => $items];
}

function json_uit(array $data): string
{
    return (string) json_encode($data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/** Vult de velden aan die de sjablonen verwachten (geescapete varianten). */
function klaar_voor_bouw(array $case, int $nummer): array
{
    $case['nummer'] = $nummer;
    $case['naamEsc'] = esc($case['naam']);
    $case['jaarEsc'] = esc($case['jaar']);
    $case['titelEsc'] = esc($case['titel']);
    $case['altEsc'] = esc($case['alt']);
    $case['bijschriftEsc'] = esc($case['bijschrift']);
    $case['omschrijvingEsc'] = esc($case['omschrijving']);
    $case['leadHtml'] = tekst_naar_html($case['lead']);
    $case['kaartHtml'] = tekst_naar_html($case['kaarttekst']);
    return $case;
}

/* ================================================================== blog */

/** Bouwt de blogpagina en alle artikelpagina's opnieuw op. */
function bouw_blog_alles(array $artikelen): array
{
    $meldingen = [];
    $zichtbaar = array_values(array_filter($artikelen, static fn(array $a): bool => !empty($a['zichtbaar'])));

    bouw_blogoverzicht($zichtbaar);
    $meldingen[] = 'Blogoverzicht bijgewerkt (' . count($zichtbaar) . ' ' . (count($zichtbaar) === 1 ? 'artikel' : 'artikelen') . ').';

    foreach ($zichtbaar as $artikel) {
        bouw_artikelpagina($artikel);
        $meldingen[] = 'Artikel /blog/' . $artikel['slug'] . ' geschreven.';
    }

    $slugs = array_column($zichtbaar, 'slug');
    foreach (glob(BLOG_MAP . '/*', GLOB_ONLYDIR) ?: [] as $map) {
        $naam = basename($map);
        if (!in_array($naam, $slugs, true) && geldige_slug($naam)) {
            @unlink($map . '/index.html');
            @rmdir($map);
            $meldingen[] = 'Oud artikel /blog/' . $naam . ' verwijderd.';
        }
    }
    return $meldingen;
}

function bouw_blogoverzicht(array $artikelen): void
{
    $pad = BLOG_MAP . '/index.html';
    $html = (string) file_get_contents($pad);

    if ($artikelen === []) {
        // Zonder artikelen komt het "binnenkort"-blok terug en blijft de pagina
        // uit Google. De tekst van dat blok staat in sjablonen/blog-leeg.html.
        $leeg = (string) file_get_contents(SJABLOON_MAP . '/blog-leeg.html');
        $html = vervang_tussen($html, 'ARTIKELEN', rtrim($leeg, "\n"));
        $html = vervang_tussen($html, 'ROBOTS', "\t\t" . '<meta name="robots" content="noindex, follow" />');
    } else {
        $kaarten = '';
        foreach ($artikelen as $artikel) {
            $datum = esc(datum_kort($artikel['datum']));
            $soort = esc($artikel['label'] !== '' ? $artikel['label'] : 'Blog');
            $kaarten .= <<<HTML
					<a class="art-kaart reveal" href="/blog/{$artikel['slug']}" data-cta="blog-{$artikel['slug']}">
						<span class="art-beeld">
							<img src="{$artikel['afbeelding']}" width="1400" height="875" loading="lazy" decoding="async" alt="{$artikel['altEsc']}" />
						</span>
						<span class="art-body">
							<span class="art-chips">
								<span class="soort">{$soort}</span>
								<span class="datum">{$datum}</span>
							</span>
							<span class="art-titel">{$artikel['titelEsc']}</span>
							<span class="art-tekst">{$artikel['samenvattingHtml']}</span>
							<span class="art-lees">
								Lees verder
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
							</span>
						</span>
					</a>

HTML;
        }
        $raster = "\t\t\t\t\t" . '<div class="artikel-raster">' . "\n" . rtrim($kaarten, "\n") . "\n\t\t\t\t\t" . '</div>';
        $html = vervang_tussen($html, 'ARTIKELEN', $raster);
        $html = vervang_tussen($html, 'ROBOTS', '');
    }

    $html = vervang_tussen($html, 'SCHEMA', schema_blog($artikelen));
    schrijf_bestand($pad, $html);
}

function bouw_artikelpagina(array $artikel): void
{
    $sjabloon = (string) file_get_contents(SJABLOON_MAP . '/artikel.html');
    $url = BASIS_URL . '/blog/' . $artikel['slug'];

    $hoofdstukken = lees_artikel($artikel['tekst']);
    $tekst = blog_naar_html($hoofdstukken, "\t\t\t\t\t");
    $datumLang = esc(datum_in_woorden($artikel['datum']));
    $soort = esc($artikel['label'] !== '' ? $artikel['label'] : 'Blog');
    $pijl = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>';

    $inhoud = <<<HTML
			<nav class="wrap kruimels" aria-label="Kruimelpad">
				<ol>
					<li><a href="/">Home</a></li>
					<li>{$pijl}</li>
					<li><a href="/blog">Blog</a></li>
					<li>{$pijl}</li>
					<li aria-current="page">{$artikel['titelEsc']}</li>
				</ol>
			</nav>

			<section class="section art-kop">
				<div class="wrap">
					<p class="art-chips">
						<span class="soort">{$soort}</span>
						<span class="datum"><time datetime="{$artikel['datum']}">{$datumLang}</time></span>
					</p>
					<h1>{$artikel['titelEsc']}</h1>
					<p class="kop-lead">{$artikel['samenvattingHtml']}</p>

					<figure class="art-hero reveal">
						<img src="{$artikel['afbeelding']}" width="1400" height="875" decoding="async" alt="{$artikel['altEsc']}" />
					</figure>
				</div>
			</section>

			<section class="section" style="padding-top: 0">
				<div class="wrap">
					<div class="lezen">
{$tekst}

						<p class="terug"><a href="/blog">&larr; Terug naar het blog</a></p>
					</div>
				</div>
			</section>

			<section class="section section-soft">
				<div class="wrap slot reveal">
					<h2>Vraag over je eigen site?</h2>
					<p>
						Vertel kort wat je bedrijf doet en wat er beter moet. Wij laten vrijblijvend zien
						wat er mogelijk is.
					</p>
					<div class="btn-rij">
						<a href="/contact" class="btn btn-primary" data-cta="artikel-contact">Gratis kennismaken <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
						<a href="/website-concept" class="btn btn-outline" data-cta="artikel-concept">Gratis websiteconcept</a>
					</div>
				</div>
			</section>
HTML;

    $html = strtr($sjabloon, [
        '{{TITEL}}'        => $artikel['titelEsc'] . ' | DH Studio',
        '{{OMSCHRIJVING}}' => $artikel['omschrijvingEsc'],
        '{{URL}}'          => $url,
        '{{BEELD}}'        => BASIS_URL . $artikel['afbeelding'],
        '{{INHOUD}}'       => $inhoud,
        '{{SCHEMA}}'       => schema_artikel($artikel, $url),
    ]);
    schrijf_bestand(BLOG_MAP . '/' . $artikel['slug'] . '/index.html', $html);
}

function schema_blog(array $artikelen): string
{
    $lijst = [];
    foreach ($artikelen as $i => $artikel) {
        $lijst[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => BASIS_URL . '/blog/' . $artikel['slug'],
            'name' => $artikel['titel'],
        ];
    }
    $pagina = [
        '@type' => 'CollectionPage',
        '@id' => BASIS_URL . '/blog#webpage',
        'url' => BASIS_URL . '/blog',
        'name' => 'Blog',
        'description' => 'Praktische stukken over wat een website wel en niet voor je kan doen.',
        'inLanguage' => 'nl-NL',
        'isPartOf' => ['@id' => BASIS_URL . '/#website'],
    ];
    if ($lijst !== []) {
        $pagina['mainEntity'] = ['@type' => 'ItemList', 'itemListElement' => $lijst];
    }
    $graaf = ['@context' => 'https://schema.org', '@graph' => [
        $pagina,
        kruimels_schema([['Home', BASIS_URL . '/'], ['Blog', BASIS_URL . '/blog']], BASIS_URL . '/blog#kruimelpad'),
    ]];
    return '<script type="application/ld+json">' . "\n" . json_uit($graaf) . "\n\t\t</script>";
}

function schema_artikel(array $artikel, string $url): string
{
    $graaf = ['@context' => 'https://schema.org', '@graph' => [
        [
            '@type' => 'BlogPosting',
            '@id' => $url . '#artikel',
            'url' => $url,
            'headline' => $artikel['titel'],
            'description' => $artikel['omschrijving'],
            'datePublished' => $artikel['datum'],
            'inLanguage' => 'nl-NL',
            'image' => BASIS_URL . $artikel['afbeelding'],
            'author' => ['@id' => BASIS_URL . '/#organisatie'],
            'publisher' => ['@id' => BASIS_URL . '/#organisatie'],
            'isPartOf' => ['@id' => BASIS_URL . '/#website'],
        ],
        kruimels_schema([
            ['Home', BASIS_URL . '/'],
            ['Blog', BASIS_URL . '/blog'],
            [$artikel['titel'], $url],
        ], $url . '#kruimelpad'),
    ]];
    return json_uit($graaf);
}

/** Vult de geescapete velden aan die de sjablonen verwachten. */
function artikel_klaar(array $artikel, int $nummer = 1): array
{
    $artikel['nummer'] = $nummer;
    $artikel['leestijd'] = leestijd($artikel['tekst'] ?? '');
    $artikel['titelEsc'] = esc($artikel['titel']);
    $artikel['altEsc'] = esc($artikel['alt']);
    $artikel['omschrijvingEsc'] = esc($artikel['omschrijving']);
    $artikel['samenvattingHtml'] = tekst_naar_html($artikel['samenvatting']);
    return $artikel;
}

/** Ruwe schatting: 200 woorden per minuut, minimaal 1. */
function leestijd(string $tekst): int
{
    $woorden = preg_split('/\s+/u', trim(strip_tags($tekst))) ?: [];
    return max(1, (int) round(count(array_filter($woorden)) / 200));
}
