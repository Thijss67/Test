<?php
/**
 * Neemt het contactformulier van /contact aan en mailt het naar DH Studio.
 *
 * Het formulier stuurde hiervoor naar /api/contact, maar dat bestond niet.
 * Elke bezoeker die het invulde kreeg "Versturen lukte niet" te zien en die
 * aanvraag was weg.
 *
 * Uitgangspunten:
 *   - niets opslaan. Het bericht gaat de deur uit en verder gebeurt er niets
 *     met de gegevens, dus er valt ook niets te lekken;
 *   - de bezoeker krijgt nooit een technische foutmelding te zien;
 *   - drie eenvoudige remmen tegen spam, zonder captcha: een honeypot, een
 *     minimale invultijd en een limiet per IP-adres.
 */
declare(strict_types=1);

const ONTVANGER   = 'info@dhstudio.nl';
const AFZENDER    = 'website@dhstudio.nl';   // moet op het eigen domein staan, anders weigert SPF de mail
const MAX_PER_UUR = 5;                        // per IP-adres
const MIN_SECONDEN = 3;                       // sneller ingevuld dan dit is geen mens

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

/** Stopt met een nette JSON-melding. */
function klaar(int $code, string $melding, bool $gelukt = false): never
{
    http_response_code($code);
    echo json_encode(['ok' => $gelukt, 'melding' => $melding], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    klaar(405, 'Alleen POST.');
}

/* ------------------------------------------------------------------ spam */

// 1. Honeypot: een veld dat onzichtbaar is voor bezoekers. Ingevuld = bot.
//    We doen alsof het gelukt is, zodat de bot niets leert.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    klaar(200, 'Bedankt, je bericht is verstuurd.', true);
}

// 2. Te snel ingevuld. Het veld is optioneel; ontbreekt het, dan slaan we
//    deze controle over in plaats van een echte bezoeker te weigeren.
$geopend = (int) ($_POST['geopend'] ?? 0);
if ($geopend > 0 && (time() - $geopend) < MIN_SECONDEN) {
    klaar(200, 'Bedankt, je bericht is verstuurd.', true);
}

// 3. Hoeveel berichten kwamen er dit uur van dit IP-adres?
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'onbekend');
$teller = sys_get_temp_dir() . '/dh-contact-' . sha1($ip . date('YmdH')) . '.tel';
$aantal = is_file($teller) ? (int) file_get_contents($teller) : 0;
if ($aantal >= MAX_PER_UUR) {
    klaar(429, 'Je hebt net al een bericht gestuurd. Mail ons gerust rechtstreeks op ' . ONTVANGER . '.');
}

/* --------------------------------------------------------------- inhoud */

$leesbaar = static function (string $sleutel, int $max): string {
    $waarde = trim((string) ($_POST[$sleutel] ?? ''));
    // Regeleindes uit velden die op één regel horen: die kunnen anders extra
    // kopregels in de mail smokkelen.
    $waarde = str_replace(["\r", "\n"], ' ', $waarde);
    return mb_substr($waarde, 0, $max);
};

$naam    = $leesbaar('naam', 100);
$bedrijf = $leesbaar('bedrijf', 100);
$email   = $leesbaar('email', 150);
$bericht = mb_substr(trim((string) ($_POST['bericht'] ?? '')), 0, 4000);

if ($naam === '' || $email === '' || $bericht === '') {
    klaar(422, 'Vul je naam, e-mailadres en bericht in.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    klaar(422, 'Dat e-mailadres klopt niet helemaal.');
}
if (mb_strlen($bericht) < 10) {
    klaar(422, 'Vertel iets meer, dan kunnen we er wat mee.');
}

/* ----------------------------------------------------------------- mail */

$onderwerp = 'Aanvraag via dhstudio.nl van ' . $naam;

$regels = [
    'Naam:    ' . $naam,
    'Bedrijf: ' . ($bedrijf !== '' ? $bedrijf : '-'),
    'E-mail:  ' . $email,
    '',
    'Bericht:',
    $bericht,
    '',
    '--',
    'Verstuurd op ' . date('d-m-Y H:i') . ' via het contactformulier op dhstudio.nl.',
];

// Reply-To op het adres van de bezoeker, zodat je direct kunt antwoorden.
// From blijft het eigen domein: een From op het adres van de bezoeker laat
// de mail bij veel providers in de spambox belanden.
$kop = [
    'From: DH Studio website <' . AFZENDER . '>',
    'Reply-To: ' . mb_encode_mimeheader($naam, 'UTF-8') . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
    'X-Mailer: dhstudio.nl',
];

$gelukt = @mail(
    ONTVANGER,
    mb_encode_mimeheader($onderwerp, 'UTF-8'),
    implode("\n", $regels),
    implode("\r\n", $kop),
    '-f' . AFZENDER
);

if (!$gelukt) {
    klaar(502, 'Versturen lukte niet. Mail ons gerust rechtstreeks op ' . ONTVANGER . '.');
}

@file_put_contents($teller, (string) ($aantal + 1), LOCK_EX);

klaar(200, 'Bedankt, je bericht is verstuurd.', true);
