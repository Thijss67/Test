<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/lib/hulp.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/bouw.php';

$actie = (string) ($_GET['actie'] ?? 'lijst');
$melding = null;
$fout = null;

/* ---------------------------------------------------------------- eerste keer */
if (!wachtwoord_ingesteld()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $wachtwoord = (string) ($_POST['wachtwoord'] ?? '');
        if (mb_strlen($wachtwoord) < 12) {
            $fout = 'Kies een wachtwoord van minstens 12 tekens.';
        } elseif ($wachtwoord !== (string) ($_POST['herhaal'] ?? '')) {
            $fout = 'De twee wachtwoorden zijn niet gelijk.';
        } else {
            bewaar_wachtwoord($wachtwoord);
            log_in();
            header('Location: ?actie=lijst');
            exit;
        }
    }
    toon_kop('Beheer instellen');
    ?>
    <div class="kaart smal">
        <h1>Beheer instellen</h1>
        <p class="uitleg">Dit paneel is nog niet in gebruik. Kies nu een wachtwoord; daarna is deze pagina afgeschermd.</p>
        <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>
        <form method="post">
            <label>Wachtwoord <input type="password" name="wachtwoord" required minlength="12" autocomplete="new-password" /></label>
            <label>Nog een keer <input type="password" name="herhaal" required minlength="12" autocomplete="new-password" /></label>
            <button class="knop" type="submit">Opslaan en inloggen</button>
        </form>
    </div>
    <?php
    toon_voet();
    exit;
}

/* ---------------------------------------------------------------- inloggen */
if ($actie === 'uitloggen') {
    log_uit();
    header('Location: ?');
    exit;
}

if (!ingelogd()) {
    $wacht = geblokkeerd();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($wacht > 0) {
            $fout = 'Te veel pogingen. Probeer het over ' . ceil($wacht / 60) . ' minuten opnieuw.';
        } elseif (controleer_wachtwoord((string) ($_POST['wachtwoord'] ?? ''))) {
            noteer_poging(true);
            log_in();
            header('Location: ?actie=lijst');
            exit;
        } else {
            noteer_poging(false);
            usleep(400000);
            $fout = 'Onjuist wachtwoord.';
        }
    }
    toon_kop('Inloggen');
    ?>
    <div class="kaart smal">
        <h1>Beheer</h1>
        <p class="uitleg">Log in om de cases te beheren.</p>
        <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>
        <form method="post">
            <label>Wachtwoord <input type="password" name="wachtwoord" required autocomplete="current-password" autofocus /></label>
            <button class="knop" type="submit">Inloggen</button>
        </form>
    </div>
    <?php
    toon_voet();
    exit;
}

/* ---------------------------------------------------------------- welk deel */
$soort = ($_GET['soort'] ?? 'cases') === 'artikelen' ? 'artikelen' : 'cases';
if ($soort === 'artikelen') {
    require __DIR__ . '/lib/artikelscherm.php';
    artikelen_scherm($actie);
    exit;
}

/* ---------------------------------------------------------------- gegevens */
$cases = lees_cases();

/* ---------------------------------------------------------------- bewerkingen */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    controleer_csrf();

    try {
        if ($actie === 'opslaan') {
            $oude_slug = (string) ($_POST['oude_slug'] ?? '');
            $case = uit_formulier($_POST);

            if ($oude_slug === '' && zoek_case($cases, $case['slug']) !== null) {
                throw new RuntimeException('Er bestaat al een case met de webadres-naam "' . $case['slug'] . '".');
            }

            $beeld = verwerk_upload($case['slug']);
            if ($beeld !== null) {
                $case['afbeelding'] = $beeld;
            } elseif ($oude_slug !== '' && ($i = zoek_case($cases, $oude_slug)) !== null) {
                $case['afbeelding'] = $cases[$i]['afbeelding'] ?? '';
            }
            if ($case['afbeelding'] === '') {
                throw new RuntimeException('Kies een schermafbeelding voor deze case.');
            }

            if ($oude_slug !== '' && ($i = zoek_case($cases, $oude_slug)) !== null) {
                $cases[$i] = $case;
                if ($oude_slug !== $case['slug']) {
                    @unlink(PORTFOLIO_MAP . '/' . $oude_slug . '/index.html');
                    @rmdir(PORTFOLIO_MAP . '/' . $oude_slug);
                }
            } else {
                array_unshift($cases, $case);
            }
            bewaar_cases($cases);
            $meldingen = publiceer($cases);
            $_SESSION['melding'] = 'Case "' . $case['naam'] . '" opgeslagen en gepubliceerd.';
            header('Location: ?actie=lijst');
            exit;
        }

        if ($actie === 'verwijderen') {
            $slug = (string) ($_POST['slug'] ?? '');
            $i = zoek_case($cases, $slug);
            if ($i === null) {
                throw new RuntimeException('Deze case bestaat niet (meer).');
            }
            $naam = $cases[$i]['naam'];
            array_splice($cases, $i, 1);
            bewaar_cases($cases);
            publiceer($cases);
            $_SESSION['melding'] = 'Case "' . $naam . '" verwijderd.';
            header('Location: ?actie=lijst');
            exit;
        }

        if ($actie === 'verplaats') {
            $slug = (string) ($_POST['slug'] ?? '');
            $richting = ($_POST['richting'] ?? '') === 'omlaag' ? 1 : -1;
            $i = zoek_case($cases, $slug);
            $j = $i === null ? null : $i + $richting;
            if ($i !== null && $j !== null && isset($cases[$j])) {
                [$cases[$i], $cases[$j]] = [$cases[$j], $cases[$i]];
                bewaar_cases($cases);
                publiceer($cases);
                $_SESSION['melding'] = 'Volgorde aangepast.';
            }
            header('Location: ?actie=lijst');
            exit;
        }

        if ($actie === 'publiceren') {
            $meldingen = publiceer($cases);
            $_SESSION['melding'] = implode(' ', $meldingen);
            header('Location: ?actie=lijst');
            exit;
        }
    } catch (Throwable $e) {
        $fout = $e->getMessage();
        $actie = ($_POST['oude_slug'] ?? '') !== '' || $actie === 'opslaan' ? 'bewerken' : 'lijst';
    }
}

if (!empty($_SESSION['melding'])) {
    $melding = (string) $_SESSION['melding'];
    unset($_SESSION['melding']);
}

/* ---------------------------------------------------------------- schermen */
if ($actie === 'nieuw' || $actie === 'bewerken') {
    $case = leeg_case();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $case = array_merge($case, uit_formulier_ruw($_POST));
        $oude_slug = (string) ($_POST['oude_slug'] ?? '');
    } else {
        $oude_slug = (string) ($_GET['slug'] ?? '');
        if ($oude_slug !== '' && ($i = zoek_case($cases, $oude_slug)) !== null) {
            $case = array_merge($case, $cases[$i]);
        } else {
            $oude_slug = '';
        }
    }
    toon_formulier($case, $oude_slug, $fout);
    exit;
}

toon_lijst($cases, $melding, $fout);

/* ================================================================ functies */

function publiceer(array $cases): array
{
    $klaar = [];
    $nummer = count($cases);
    foreach ($cases as $case) {
        $klaar[] = klaar_voor_bouw($case, $nummer--);
    }
    return bouw_alles($klaar);
}

function leeg_case(): array
{
    return [
        'slug' => '', 'naam' => '', 'jaar' => date('Y'), 'labels' => [],
        'kaarttekst' => '', 'afbeelding' => '', 'alt' => '',
        'titel' => '', 'omschrijving' => '', 'lead' => '', 'bijschrift' => '',
        'feiten' => [], 'artikel' => '', 'zichtbaar' => true,
    ];
}

/** Leest het formulier zonder te controleren (om het opnieuw te kunnen tonen). */
function uit_formulier_ruw(array $post): array
{
    $regels = static fn(string $tekst): array => array_values(array_filter(
        array_map('trim', preg_split('/\R/u', $tekst) ?: []),
        static fn(string $r): bool => $r !== ''
    ));

    $feiten = [];
    foreach ($regels((string) ($post['feiten'] ?? '')) as $regel) {
        [$label, $waarde] = array_pad(array_map('trim', explode('|', $regel, 2)), 2, '');
        if ($label !== '') {
            $feiten[] = ['label' => $label, 'waarde' => $waarde];
        }
    }
    $labels = array_values(array_filter(array_map('trim', explode(',', (string) ($post['labels'] ?? '')))));

    return [
        'slug' => maak_slug((string) ($post['slug'] ?? '')) ?: maak_slug((string) ($post['naam'] ?? '')),
        'naam' => trim((string) ($post['naam'] ?? '')),
        'jaar' => trim((string) ($post['jaar'] ?? '')),
        'labels' => $labels,
        'kaarttekst' => trim((string) ($post['kaarttekst'] ?? '')),
        'alt' => trim((string) ($post['alt'] ?? '')),
        'titel' => trim((string) ($post['titel'] ?? '')),
        'omschrijving' => trim((string) ($post['omschrijving'] ?? '')),
        'lead' => trim((string) ($post['lead'] ?? '')),
        'bijschrift' => trim((string) ($post['bijschrift'] ?? '')),
        'feiten' => $feiten,
        'artikel' => rtrim((string) ($post['artikel'] ?? '')),
        'zichtbaar' => !empty($post['zichtbaar']),
        'afbeelding' => '',
    ];
}

/** Leest en controleert het formulier. */
function uit_formulier(array $post): array
{
    $case = uit_formulier_ruw($post);

    if ($case['naam'] === '') {
        throw new RuntimeException('Vul de naam van de klant of het project in.');
    }
    if (!geldige_slug($case['slug'])) {
        throw new RuntimeException('De webadres-naam mag alleen kleine letters, cijfers en streepjes bevatten.');
    }
    if ($case['titel'] === '') {
        throw new RuntimeException('Vul een titel voor de casepagina in.');
    }
    if ($case['omschrijving'] === '') {
        throw new RuntimeException('Vul een korte omschrijving in; die gebruikt Google in het zoekresultaat.');
    }
    if ($case['alt'] === '') {
        $case['alt'] = 'Schermafbeelding van de website van ' . $case['naam'];
    }
    if (mb_strlen($case['omschrijving']) > 160) {
        throw new RuntimeException('Houd de omschrijving onder de 160 tekens (nu ' . mb_strlen($case['omschrijving']) . ').');
    }
    return $case;
}

/** Slaat een geuploade schermafbeelding op onder assets/werk-<slug>.<ext>. */
function verwerk_upload(string $slug, string $voorvoegsel = 'werk-'): ?string
{
    if (empty($_FILES['beeld']['name'])) {
        return null;
    }
    $bestand = $_FILES['beeld'];
    if (($bestand['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($bestand['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Het uploaden van de afbeelding is misgegaan (code ' . $bestand['error'] . ').');
    }
    if ($bestand['size'] > MAX_BEELD) {
        throw new RuntimeException('De afbeelding is groter dan ' . round(MAX_BEELD / 1048576) . ' MB.');
    }
    $info = @getimagesize($bestand['tmp_name']);
    $soorten = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($soorten[$info[2]])) {
        throw new RuntimeException('Alleen JPG, PNG of WebP zijn toegestaan.');
    }
    $ext = $soorten[$info[2]];
    $doel = ASSETS_MAP . '/' . $voorvoegsel . $slug . '.' . $ext;
    if (!is_dir(ASSETS_MAP) && !mkdir(ASSETS_MAP, 0755, true) && !is_dir(ASSETS_MAP)) {
        throw new RuntimeException('De map /assets bestaat niet en kan niet worden aangemaakt.');
    }
    if (!move_uploaded_file($bestand['tmp_name'], $doel)) {
        throw new RuntimeException('De afbeelding kon niet worden opgeslagen. Controleer de rechten op /assets.');
    }
    @chmod($doel, 0644);
    // oudere versie met een andere extensie opruimen
    foreach (['jpg', 'png', 'webp'] as $oud) {
        if ($oud !== $ext) {
            @unlink(ASSETS_MAP . '/' . $voorvoegsel . $slug . '.' . $oud);
        }
    }
    return '/assets/' . $voorvoegsel . $slug . '.' . $ext;
}

/* ---------------------------------------------------------------- weergave */

function toon_kop(string $titel): void
{
    ?><!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex, nofollow" />
<title><?= esc($titel) ?> · DH Studio</title>
<style>
	:root { --navy:#0d1b3e; --accent:#2f6bff; --accent-text:#1d4ed8; --line:#e7e9f0; --body:#5c6577; --muted:#858c9c; --bg:#fbfaf7; }
	* { box-sizing: border-box; }
	body { margin:0; background:var(--bg); color:var(--navy); font:16px/1.55 system-ui, -apple-system, "Segoe UI", sans-serif; }
	.balk { background:var(--navy); color:#fff; padding:0.9rem 1.25rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; }
	.balk b { font-size:1rem; letter-spacing:-0.01em; }
	.balk a { color:rgba(255,255,255,.8); text-decoration:none; font-size:.9rem; }
	.balk a:hover { color:#fff; }
	.balk .rechts { margin-left:auto; display:flex; gap:1rem; align-items:center; }
	main { max-width:60rem; margin:0 auto; padding:1.75rem 1.25rem 4rem; }
	.kaart { background:#fff; border:1px solid var(--line); border-radius:14px; padding:1.5rem; }
	.kaart.smal { max-width:26rem; margin:3rem auto; }
	h1 { font-size:1.6rem; letter-spacing:-0.02em; margin:0 0 .35rem; }
	h2 { font-size:1.05rem; margin:2rem 0 .75rem; }
	.uitleg { color:var(--body); font-size:.92rem; margin:0 0 1.25rem; }
	label { display:block; margin:0 0 1rem; font-size:.85rem; font-weight:600; color:var(--navy); }
	input[type=text], input[type=password], textarea, input[type=file] {
		display:block; width:100%; margin-top:.35rem; padding:.6rem .7rem; font:inherit; font-weight:400;
		border:1px solid #d8dce7; border-radius:9px; background:#fff; color:var(--navy);
	}
	textarea { min-height:7rem; resize:vertical; font-size:.92rem; }
	textarea.groot { min-height:24rem; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:.86rem; line-height:1.6; }
	.hint { display:block; margin-top:.3rem; font-weight:400; font-size:.8rem; color:var(--muted); }
	.knop { display:inline-flex; align-items:center; gap:.4rem; border:0; cursor:pointer; padding:.65rem 1.15rem;
		border-radius:999px; background:var(--accent); color:#fff; font:inherit; font-weight:600; font-size:.92rem; text-decoration:none; }
	.knop:hover { background:var(--accent-text); }
	.knop.stil { background:#fff; color:var(--navy); border:1px solid #d8dce7; }
	.knop.stil:hover { background:#f2f4f9; }
	.knop.rood { background:#fff; color:#b42318; border:1px solid #f3c7c2; }
	.knop.rood:hover { background:#fef3f2; }
	.rij { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
	.melding, .fout { border-radius:10px; padding:.8rem 1rem; font-size:.92rem; margin:0 0 1.25rem; }
	.melding { background:#eefbf3; border:1px solid #bfe8d0; color:#11623a; }
	.fout { background:#fef3f2; border:1px solid #f3c7c2; color:#b42318; }
	table { width:100%; border-collapse:collapse; }
	th, td { text-align:left; padding:.75rem .5rem; border-bottom:1px solid var(--line); vertical-align:middle; font-size:.92rem; }
	th { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); }
	td img { display:block; width:6.5rem; height:auto; border-radius:6px; border:1px solid var(--line); }
	.uit { color:var(--muted); font-size:.8rem; }
	.tabs { display:inline-flex; gap:.25rem; padding:.3rem; margin-bottom:1.25rem; background:#fff; border:1px solid var(--line); border-radius:999px; }
	.tabs a { padding:.5rem 1rem; border-radius:999px; font-size:.9rem; font-weight:600; color:var(--body); text-decoration:none; }
	.tabs a:hover { background:#f2f4f9; color:var(--navy); }
	.tabs a.aan { background:var(--navy); color:#fff; }
	.tweekolom { display:grid; gap:0 1.25rem; grid-template-columns:1fr; }
	@media (min-width:52rem) { .tweekolom { grid-template-columns:1fr 1fr; } }
</style>
</head>
<body>
<div class="balk">
	<b>DH Studio · beheer</b>
	<?php if (wachtwoord_ingesteld() && ingelogd()): ?>
	<div class="rechts">
		<a href="/portfolio" target="_blank" rel="noopener">Bekijk portfolio</a>
		<a href="?actie=uitloggen">Uitloggen</a>
	</div>
	<?php endif; ?>
</div>
<main>
<?php
}

function tabbladen(string $actief): string
{
    $items = ['cases' => 'Cases', 'artikelen' => 'Blogartikelen'];
    $uit = '<div class="tabs">';
    foreach ($items as $sleutel => $naam) {
        $adres = $sleutel === 'cases' ? '?actie=lijst' : '?actie=lijst&amp;soort=artikelen';
        $klasse = $sleutel === $actief ? ' class="aan"' : '';
        $uit .= '<a href="' . $adres . '"' . $klasse . '>' . $naam . '</a>';
    }
    return $uit . '</div>';
}

function toon_voet(): void
{
    echo "</main>\n</body>\n</html>\n";
}

function toon_lijst(array $cases, ?string $melding, ?string $fout): void
{
    toon_kop('Cases');
    ?>
    <?= tabbladen('cases') ?>
    <div class="rij" style="justify-content:space-between; margin-bottom:1.25rem">
        <h1 style="margin:0">Cases</h1>
        <div class="rij">
            <form method="post" action="?actie=publiceren"><?= csrf_veld() ?><button class="knop stil" type="submit">Opnieuw publiceren</button></form>
            <a class="knop" href="?actie=nieuw">Nieuwe case</a>
        </div>
    </div>
    <?php if ($melding): ?><p class="melding"><?= esc($melding) ?></p><?php endif; ?>
    <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>

    <div class="kaart">
        <?php if (!$cases): ?>
            <p class="uitleg" style="margin:0">Nog geen cases. Maak er een aan met <a href="?actie=nieuw">Nieuwe case</a>.</p>
        <?php else: ?>
        <table>
            <tr><th>Beeld</th><th>Case</th><th>Jaar</th><th>Volgorde</th><th></th></tr>
            <?php foreach ($cases as $i => $case): ?>
            <tr>
                <td><?php if (!empty($case['afbeelding'])): ?><img src="<?= esc($case['afbeelding']) ?>" alt="" /><?php endif; ?></td>
                <td>
                    <b><?= esc($case['naam']) ?></b><br />
                    <span class="uit">/portfolio/<?= esc($case['slug']) ?><?= empty($case['zichtbaar']) ? ' · verborgen' : '' ?></span>
                </td>
                <td><?= esc((string) ($case['jaar'] ?? '')) ?></td>
                <td>
                    <div class="rij">
                        <?php if ($i > 0): ?>
                        <form method="post" action="?actie=verplaats"><?= csrf_veld() ?>
                            <input type="hidden" name="slug" value="<?= esc($case['slug']) ?>" />
                            <input type="hidden" name="richting" value="omhoog" />
                            <button class="knop stil" type="submit" title="Naar boven">&uarr;</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($i < count($cases) - 1): ?>
                        <form method="post" action="?actie=verplaats"><?= csrf_veld() ?>
                            <input type="hidden" name="slug" value="<?= esc($case['slug']) ?>" />
                            <input type="hidden" name="richting" value="omlaag" />
                            <button class="knop stil" type="submit" title="Naar beneden">&darr;</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="rij" style="justify-content:flex-end">
                        <a class="knop stil" href="?actie=bewerken&amp;slug=<?= urlencode($case['slug']) ?>">Wijzigen</a>
                        <form method="post" action="?actie=verwijderen" onsubmit="return confirm('Case &quot;<?= esc($case['naam']) ?>&quot; verwijderen? De pagina verdwijnt van de site.');">
                            <?= csrf_veld() ?>
                            <input type="hidden" name="slug" value="<?= esc($case['slug']) ?>" />
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
        Bij elke wijziging schrijft het paneel de HTML-bestanden opnieuw weg. Bezoekers krijgen dus
        gewone, statische pagina's te zien &mdash; net zo snel als daarvoor.
    </p>
    <?php
    toon_voet();
}

function toon_formulier(array $case, string $oude_slug, ?string $fout): void
{
    $feiten = '';
    foreach ($case['feiten'] as $feit) {
        $feiten .= $feit['label'] . ' | ' . $feit['waarde'] . "\n";
    }
    toon_kop($oude_slug === '' ? 'Nieuwe case' : 'Case wijzigen');
    ?>
    <div class="rij" style="justify-content:space-between; margin-bottom:1.25rem">
        <h1 style="margin:0"><?= $oude_slug === '' ? 'Nieuwe case' : esc($case['naam']) ?></h1>
        <a class="knop stil" href="?actie=lijst">Terug</a>
    </div>
    <?php if ($fout): ?><p class="fout"><?= esc($fout) ?></p><?php endif; ?>

    <form method="post" action="?actie=opslaan" enctype="multipart/form-data">
        <?= csrf_veld() ?>
        <input type="hidden" name="oude_slug" value="<?= esc($oude_slug) ?>" />

        <div class="kaart">
            <h2 style="margin-top:0">Op de portfoliopagina</h2>
            <div class="tweekolom">
                <label>Naam van de klant
                    <input type="text" name="naam" value="<?= esc($case['naam']) ?>" required />
                </label>
                <label>Jaar
                    <input type="text" name="jaar" value="<?= esc((string) $case['jaar']) ?>" />
                </label>
            </div>
            <label>Webadres
                <input type="text" name="slug" value="<?= esc($case['slug']) ?>" placeholder="bakkerij-de-vries" />
                <span class="hint">De pagina komt op /portfolio/&lt;webadres&gt;. Laat leeg om hem uit de naam te maken.</span>
            </label>
            <label>Labels
                <input type="text" name="labels" value="<?= esc(implode(', ', $case['labels'])) ?>" placeholder="Website laten maken, SEO" />
                <span class="hint">Gescheiden door komma's. Verschijnen als kleine tekstjes op de kaart.</span>
            </label>
            <label>Tekst op de kaart
                <textarea name="kaarttekst" style="min-height:5rem"><?= esc($case['kaarttekst']) ?></textarea>
                <span class="hint">Twee tot drie regels. **tekst tussen sterretjes** wordt vet.</span>
            </label>
            <label>Schermafbeelding
                <input type="file" name="beeld" accept="image/jpeg,image/png,image/webp" />
                <span class="hint">
                    Liefst 1400 &times; 875 pixels, JPG of WebP.
                    <?php if (!empty($case['afbeelding'])): ?>Nu ingesteld: <?= esc($case['afbeelding']) ?>. Leeg laten = houden.<?php endif; ?>
                </span>
            </label>
            <label>Beschrijving van de afbeelding
                <input type="text" name="alt" value="<?= esc($case['alt']) ?>" placeholder="De homepage van bakkerijdevries.nl" />
                <span class="hint">Voor blinde bezoekers en Google. Beschrijf wat er te zien is.</span>
            </label>
            <label style="font-weight:600">
                <input type="checkbox" name="zichtbaar" value="1" style="width:auto; display:inline; margin-right:.4rem" <?= !empty($case['zichtbaar']) ? 'checked' : '' ?> />
                Tonen op de site
                <span class="hint">Zet uit om een case te bewaren zonder hem te publiceren.</span>
            </label>
        </div>

        <div class="kaart" style="margin-top:1.25rem">
            <h2 style="margin-top:0">Bovenaan de casepagina</h2>
            <label>Titel
                <input type="text" name="titel" value="<?= esc($case['titel']) ?>" required placeholder="Een nieuwe site voor een bakkerij met drie filialen" />
            </label>
            <label>Omschrijving voor Google
                <input type="text" name="omschrijving" value="<?= esc($case['omschrijving']) ?>" required maxlength="160" />
                <span class="hint">Maximaal 160 tekens. Dit staat onder de blauwe link in het zoekresultaat.</span>
            </label>
            <label>Inleiding
                <textarea name="lead"><?= esc($case['lead']) ?></textarea>
                <span class="hint">Drie tot vijf regels: waar ging het om en wat hebben jullie gedaan.</span>
            </label>
            <label>Feiten
                <textarea name="feiten" style="min-height:6rem"><?= esc(rtrim($feiten)) ?></textarea>
                <span class="hint">Een per regel, met een liggend streepje ertussen: <code>Klant | Bakkerij de Vries</code>. Een link maak je zo: <code>[bakkerij.nl](https://bakkerij.nl)</code>.</span>
            </label>
            <label>Bijschrift onder de afbeelding
                <input type="text" name="bijschrift" value="<?= esc($case['bijschrift']) ?>" />
            </label>
        </div>

        <div class="kaart" style="margin-top:1.25rem">
            <h2 style="margin-top:0">Het verhaal</h2>
            <p class="uitleg">
                Elk hoofdstuk begint met <code># </code> en krijgt vanzelf een nummer en een plek in de
                inhoudsopgave. Verder kun je dit gebruiken:
            </p>
            <ul class="uitleg" style="margin-top:-.75rem">
                <li><code>## Kop</code> &mdash; een keuze met een eigen kopje eronder</li>
                <li><code>&gt; tekst</code> &mdash; een uitspraak, groot uitgelicht</li>
                <li><code>- **Vet.** rest</code> &mdash; een opsomming</li>
                <li><code>= 42 kB | homepage</code> &mdash; een kerngetal (meerdere regels achter elkaar worden een blok)</li>
                <li>een lege regel scheidt alinea's</li>
            </ul>
            <label>Tekst
                <textarea class="groot" name="artikel"><?= esc($case['artikel']) ?></textarea>
            </label>
        </div>

        <div class="rij" style="margin-top:1.25rem">
            <button class="knop" type="submit">Opslaan en publiceren</button>
            <a class="knop stil" href="?actie=lijst">Annuleren</a>
        </div>
    </form>
    <?php
    toon_voet();
}
