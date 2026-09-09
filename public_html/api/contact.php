<?php
/**
 * Neemt het contactformulier van /contact aan en mailt het naar DH Studio.
 *
 * Het formulier stuurde hiervoor naar /api/contact, maar dat bestond niet.
 * Elke bezoeker die het invulde kreeg "Versturen lukte niet" te zien en die
 * aanvraag was weg.
 *
 * Er wordt niets opgeslagen: het bericht gaat de deur uit en daarmee is het
 * klaar. De gedeelde afhandeling staat in verzenden.php.
 */
declare(strict_types=1);

require __DIR__ . '/verzenden.php';

begin();
$teller = rem('contact');

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

/* --------------------------------------------------------------- inhoud */

$naam    = een_regel((string) ($_POST['naam'] ?? ''), 100);
$bedrijf = een_regel((string) ($_POST['bedrijf'] ?? ''), 100);
$email   = een_regel((string) ($_POST['email'] ?? ''), 150);
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

$tekst = implode("\n", [
    'Naam:    ' . $naam,
    'Bedrijf: ' . ($bedrijf !== '' ? $bedrijf : '-'),
    'E-mail:  ' . $email,
    '',
    'Bericht:',
    $bericht,
    '',
    '--',
    'Verstuurd op ' . date('d-m-Y H:i') . ' via het contactformulier op dhstudio.nl.',
]);

if (!verstuur('Aanvraag via dhstudio.nl van ' . $naam, $tekst, $naam, $email)) {
    klaar(502, 'Versturen lukte niet. Mail ons gerust rechtstreeks op ' . ONTVANGER . '.');
}

tel_op($teller);
klaar(200, 'Bedankt, je bericht is verstuurd.', true);
