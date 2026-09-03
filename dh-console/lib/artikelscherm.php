<?php
/** Het scherm voor blogartikelen. Werkt hetzelfde als dat voor cases. */

function artikelen_scherm(string $actie): void
{
    $artikelen = lees_artikelen();
    $fout = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        controleer_csrf();
        try {
            if ($actie === 'opslaan') {
                $oude_slug = (string) ($_POST['oude_slug'] ?? '');
                $artikel = artikel_uit_formulier($_POST);

                if ($oude_slug === '' && zoek_op_slug($artikelen, $artikel['slug']) !== null) {
                    throw new RuntimeException('Er bestaat al een artikel met de webadres-naam "' . $artikel['slug'] . '".');
                }

                $beeld = verwerk_upload($artikel['slug'], 'blog-');
                if ($beeld !== null) {
                    $artikel['afbeelding'] = $beeld;
                } elseif ($oude_slug !== '' && ($i = zoek_op_slug($artikelen, $oude_slug)) !== null) {
                    $artikel['afbeelding'] = $artikelen[$i]['afbeelding'] ?? '';
                }
                if ($artikel['afbeelding'] === '') {
                    throw new RuntimeException('Kies een afbeelding bij dit artikel.');
                }

                if ($oude_slug !== '' && ($i = zoek_op_slug($artikelen, $oude_slug)) !== null) {
                    $artikelen[$i] = $artikel;
                    if ($oude_slug !== $artikel['slug']) {
                        @unlink(BLOG_MAP . '/' . $oude_slug . '/index.html');
                        @rmdir(BLOG_MAP . '/' . $oude_slug);
                    }
                } else {
                    array_unshift($artikelen, $artikel);
                }
                sorteer_artikelen($artikelen);
                bewaar_artikelen($artikelen);
                publiceer_artikelen($artikelen);
                $_SESSION['melding'] = 'Artikel "' . $artikel['titel'] . '" opgeslagen en gepubliceerd.';
                header('Location: ?actie=lijst&soort=artikelen');
                exit;
            }

            if ($actie === 'verwijderen') {
                $slug = (string) ($_POST['slug'] ?? '');
                $i = zoek_op_slug($artikelen, $slug);
                if ($i === null) {
                    throw new RuntimeException('Dit artikel bestaat niet (meer).');
                }
                $titel = $artikelen[$i]['titel'];
                array_splice($artikelen, $i, 1);
                bewaar_artikelen($artikelen);
                publiceer_artikelen($artikelen);
                $_SESSION['melding'] = 'Artikel "' . $titel . '" verwijderd.';
                header('Location: ?actie=lijst&soort=artikelen');
                exit;
            }

            if ($actie === 'publiceren') {
                $meldingen = publiceer_artikelen($artikelen);
                $_SESSION['melding'] = implode(' ', $meldingen);
                header('Location: ?actie=lijst&soort=artikelen');
                exit;
            }
        } catch (Throwable $e) {
            $fout = $e->getMessage();
            $actie = 'bewerken';
        }
    }

    $melding = null;
    if (!empty($_SESSION['melding'])) {
        $melding = (string) $_SESSION['melding'];
        unset($_SESSION['melding']);
    }

    if ($actie === 'nieuw' || $actie === 'bewerken') {
        $artikel = leeg_artikel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $artikel = array_merge($artikel, artikel_uit_formulier_ruw($_POST));
            $oude_slug = (string) ($_POST['oude_slug'] ?? '');
        } else {
            $oude_slug = (string) ($_GET['slug'] ?? '');
            if ($oude_slug !== '' && ($i = zoek_op_slug($artikelen, $oude_slug)) !== null) {
                $artikel = array_merge($artikel, $artikelen[$i]);
            } else {
                $oude_slug = '';
            }
        }
        toon_artikelformulier($artikel, $oude_slug, $fout);
        return;
    }

    toon_artikellijst($artikelen, $melding, $fout);
}

/** Nieuwste bovenaan. */
function sorteer_artikelen(array &$artikelen): void
{
    usort($artikelen, static fn(array $a, array $b): int => strcmp($b['datum'] ?? '', $a['datum'] ?? ''));
}

function publiceer_artikelen(array $artikelen): array
{
    $klaar = [];
    $nummer = 1;
    foreach ($artikelen as $artikel) {
        $klaar[] = artikel_klaar($artikel, $nummer++);
    }
    return bouw_blog_alles($klaar);
}

function leeg_artikel(): array
{
    return [
        'slug' => '', 'titel' => '', 'datum' => date('Y-m-d'), 'label' => 'Blog',
        'samenvatting' => '', 'omschrijving' => '', 'afbeelding' => '', 'alt' => '',
        'tekst' => '', 'zichtbaar' => true,
    ];
}

function artikel_uit_formulier_ruw(array $post): array
{
    return [
        'slug' => maak_slug((string) ($post['slug'] ?? '')) ?: maak_slug((string) ($post['titel'] ?? '')),
        'titel' => trim((string) ($post['titel'] ?? '')),
        'datum' => trim((string) ($post['datum'] ?? '')),
        'label' => trim((string) ($post['label'] ?? '')) ?: 'Blog',
        'samenvatting' => trim((string) ($post['samenvatting'] ?? '')),
        'omschrijving' => trim((string) ($post['omschrijving'] ?? '')),
        'alt' => trim((string) ($post['alt'] ?? '')),
        'tekst' => rtrim((string) ($post['tekst'] ?? '')),
        'zichtbaar' => !empty($post['zichtbaar']),
        'afbeelding' => '',
    ];
}

function artikel_uit_formulier(array $post): array
{
    $artikel = artikel_uit_formulier_ruw($post);

    if ($artikel['titel'] === '') {
        throw new RuntimeException('Vul een titel in.');
    }
    if (!geldige_slug($artikel['slug'])) {
        throw new RuntimeException('De webadres-naam mag alleen kleine letters, cijfers en streepjes bevatten.');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $artikel['datum'])) {
        throw new RuntimeException('Vul een geldige datum in (jjjj-mm-dd).');
    }
    if ($artikel['samenvatting'] === '') {
        throw new RuntimeException('Vul een korte samenvatting in; die staat op de kaart en boven het artikel.');
    }
    if ($artikel['omschrijving'] === '') {
        $artikel['omschrijving'] = mb_substr($artikel['samenvatting'], 0, 155);
    }
    if (mb_strlen($artikel['omschrijving']) > 160) {
        throw new RuntimeException('Houd de omschrijving voor Google onder de 160 tekens (nu ' . mb_strlen($artikel['omschrijving']) . ').');
    }
    if ($artikel['alt'] === '') {
        $artikel['alt'] = 'Afbeelding bij het artikel ' . $artikel['titel'];
    }
    if ($artikel['tekst'] === '') {
        throw new RuntimeException('Het artikel heeft nog geen tekst.');
    }
    return $artikel;
}

function toon_artikellijst(array $artikelen, ?string $melding, ?string $fout): void
{
    toon_kop('Blogartikelen');
    echo tabbladen('artikelen');
    ?>
    <div class="rij" style="justify-content:space-between; margin-bottom:1.25rem">
        <h1 style="margin:0">Blogartikelen</h1>
        <div class="rij">
            <form method="post" action="?actie=publiceren&amp;soort=artikelen"><?= csrf_veld() ?><button class="knop stil" type="submit">Opnieuw publiceren</button></form>
            <a class="knop" href="?actie=nieuw&amp;soort=artikelen">Nieuw artikel</a>
        </div>
    </div>
    <?php if ($melding): ?><p class="melding"><?= esc($melding) ?></p><?php endif; ?>
    <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>

    <div class="kaart">
        <?php if (!$artikelen): ?>
            <p class="uitleg" style="margin:0">
                Nog geen artikelen. Zolang deze lijst leeg is, blijft op de blogpagina het blok
                &ldquo;De eerste artikelen komen eraan&rdquo; staan en blijft de pagina uit Google.
                Zodra je het eerste artikel publiceert, verdwijnt dat vanzelf.
            </p>
        <?php else: ?>
        <table>
            <tr><th>Beeld</th><th>Artikel</th><th>Datum</th><th></th></tr>
            <?php foreach ($artikelen as $artikel): ?>
            <tr>
                <td><?php if (!empty($artikel['afbeelding'])): ?><img src="<?= esc($artikel['afbeelding']) ?>" alt="" /><?php endif; ?></td>
                <td>
                    <b><?= esc($artikel['titel']) ?></b><br />
                    <span class="uit">/blog/<?= esc($artikel['slug']) ?><?= empty($artikel['zichtbaar']) ? ' · verborgen' : '' ?></span>
                </td>
                <td><?= esc(datum_kort($artikel['datum'])) ?></td>
                <td>
                    <div class="rij" style="justify-content:flex-end">
                        <a class="knop stil" href="?actie=bewerken&amp;soort=artikelen&amp;slug=<?= urlencode($artikel['slug']) ?>">Wijzigen</a>
                        <form method="post" action="?actie=verwijderen&amp;soort=artikelen" onsubmit="return confirm('Artikel &quot;<?= esc($artikel['titel']) ?>&quot; verwijderen?');">
                            <?= csrf_veld() ?>
                            <input type="hidden" name="slug" value="<?= esc($artikel['slug']) ?>" />
                            <button class="knop rood" type="submit">Verwijderen</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <p class="uitleg" style="margin-top:1.25rem">
        Artikelen staan op datum, nieuwste bovenaan. Ook hier schrijft het paneel gewone HTML weg.
    </p>
    <?php
    toon_voet();
}

function toon_artikelformulier(array $artikel, string $oude_slug, ?string $fout): void
{
    toon_kop($oude_slug === '' ? 'Nieuw artikel' : 'Artikel wijzigen');
    ?>
    <div class="rij" style="justify-content:space-between; margin-bottom:1.25rem">
        <h1 style="margin:0"><?= $oude_slug === '' ? 'Nieuw artikel' : esc($artikel['titel']) ?></h1>
        <a class="knop stil" href="?actie=lijst&amp;soort=artikelen">Terug</a>
    </div>
    <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>

    <form method="post" action="?actie=opslaan&amp;soort=artikelen" enctype="multipart/form-data">
        <?= csrf_veld() ?>
        <input type="hidden" name="oude_slug" value="<?= esc($oude_slug) ?>" />

        <div class="kaart">
            <h2 style="margin-top:0">Op de blogpagina</h2>
            <label>Titel
                <input type="text" name="titel" value="<?= esc($artikel['titel']) ?>" required />
            </label>
            <div class="tweekolom">
                <label>Datum
                    <input type="text" name="datum" value="<?= esc($artikel['datum']) ?>" placeholder="2026-08-18" required />
                    <span class="hint">Jaar-maand-dag. Bepaalt de volgorde.</span>
                </label>
                <label>Label
                    <input type="text" name="label" value="<?= esc($artikel['label']) ?>" placeholder="Blog" />
                    <span class="hint">Staat als gekleurd tekstje op de kaart. Bijvoorbeeld Blog, Uitleg of Nieuws.</span>
                </label>
            </div>
            <label>Webadres
                <input type="text" name="slug" value="<?= esc($artikel['slug']) ?>" placeholder="wat-kost-een-website" />
                <span class="hint">Het artikel komt op /blog/&lt;webadres&gt;. Laat leeg om het uit de titel te maken.</span>
            </label>
            <label>Samenvatting
                <textarea name="samenvatting" style="min-height:5rem" required><?= esc($artikel['samenvatting']) ?></textarea>
                <span class="hint">Twee zinnen. Staat op de kaart en bovenaan het artikel.</span>
            </label>
            <label>Afbeelding
                <input type="file" name="beeld" accept="image/jpeg,image/png,image/webp" />
                <span class="hint">
                    Liefst 1400 &times; 875 pixels.
                    <?php if (!empty($artikel['afbeelding'])): ?>Nu ingesteld: <?= esc($artikel['afbeelding']) ?>. Leeg laten = houden.<?php endif; ?>
                </span>
            </label>
            <label>Beschrijving van de afbeelding
                <input type="text" name="alt" value="<?= esc($artikel['alt']) ?>" />
            </label>
            <label>Omschrijving voor Google
                <input type="text" name="omschrijving" value="<?= esc($artikel['omschrijving']) ?>" maxlength="160" />
                <span class="hint">Leeg laten? Dan gebruiken we de samenvatting.</span>
            </label>
            <label style="font-weight:600">
                <input type="checkbox" name="zichtbaar" value="1" style="width:auto; display:inline; margin-right:.4rem" <?= !empty($artikel['zichtbaar']) ? 'checked' : '' ?> />
                Tonen op de site
                <span class="hint">Zet uit om aan een concept te werken zonder het te publiceren.</span>
            </label>
        </div>

        <div class="kaart" style="margin-top:1.25rem">
            <h2 style="margin-top:0">Het artikel</h2>
            <p class="uitleg">
                Dezelfde opmaak als bij een case: <code># Kop</code> voor een tussenkop,
                <code>## Kop</code> voor een kleinere kop, <code>&gt; tekst</code> voor een uitspraak,
                <code>- punt</code> voor een opsomming, <code>= 3 | label</code> voor kerngetallen,
                <code>**vet**</code> en <code>[tekst](https://…)</code>. Een lege regel maakt een nieuwe alinea.
            </p>
            <label>Tekst
                <textarea class="groot" name="tekst" required><?= esc($artikel['tekst']) ?></textarea>
            </label>
        </div>

        <div class="rij" style="margin-top:1.25rem">
            <button class="knop" type="submit">Opslaan en publiceren</button>
            <a class="knop stil" href="?actie=lijst&amp;soort=artikelen">Annuleren</a>
        </div>
    </form>
    <?php
    toon_voet();
}
