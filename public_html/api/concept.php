<?php
/**
 * Neemt de aanvraag van /website-concept aan.
 *
 * De wizard stuurde hiervoor naar een leeg adres. Daardoor kwam je na zeven
 * vragen niet op "Dankjewel voor je aanvraag" uit, maar op "Bijna klaar" met
 * een mailto-knop die je zelf nog moest aanklikken. Wie dat niet deed, was
 * weg -- en dat merkte je nergens aan.
 *
 * Anders dan het contactformulier stuurt de wizard JSON, met de vraag als
 * sleutel en het antwoord als waarde. De vragen kunnen veranderen zonder dat
 * dit bestand aangepast hoeft te worden.
 */
declare(strict_types=1);

require __DIR__ . '/verzenden.php';

begin();
$teller = rem('concept');

$ruw = file_get_contents('php://input');
if ($ruw === false || strlen($ruw) > 20000) {
    klaar(413, 'Je aanvraag is te groot om te versturen.');
}

$data = json_decode((string) $ruw, true);
if (!is_array($data) || $data === []) {
    klaar(422, 'We konden je antwoorden niet lezen. Probeer het opnieuw.');
}

/* ------------------------------------------------------------- uitlezen */

// De wizard levert de vraag als sleutel. Alles wat geen platte tekst is,
// laten we vallen: er hoort niets anders in te zitten.
$rijen = [];
foreach ($data as $vraag => $antwoord) {
    if (!is_string($vraag) || !is_scalar($antwoord)) {
        continue;
    }
    $vraag = een_regel((string) $vraag, 80);
    $antwoord = mb_substr(trim((string) $antwoord), 0, 2000);
    if ($vraag !== '' && $antwoord !== '') {
        $rijen[$vraag] = $antwoord;
    }
    if (count($rijen) >= 40) {
        break;
    }
}

if (count($rijen) < 2) {
    klaar(422, 'Er kwamen te weinig antwoorden binnen. Probeer het opnieuw.');
}

// Naam en e-mailadres opzoeken tussen de antwoorden, voor het onderwerp en
// het Reply-To. Lukt dat niet, dan gaat de aanvraag gewoon zonder mee.
$zoek = static function (array $rijen, array $woorden): string {
    foreach ($rijen as $vraag => $antwoord) {
        foreach ($woorden as $woord) {
            if (mb_stripos($vraag, $woord) !== false) {
                return $antwoord;
            }
        }
    }
    return '';
};
$naam  = een_regel($zoek($rijen, ['naam']), 100);
$email = een_regel($zoek($rijen, ['e-mail', 'email']), 150);

/* ----------------------------------------------------------------- mail */

$tekst = [];
foreach ($rijen as $vraag => $antwoord) {
    $tekst[] = $vraag . ':';
    $tekst[] = $antwoord;
    $tekst[] = '';
}
$tekst[] = '--';
$tekst[] = 'Aangevraagd op ' . date('d-m-Y H:i') . ' via het websiteconcept-formulier op dhstudio.nl.';

$onderwerp = 'Gratis websiteconcept aangevraagd' . ($naam !== '' ? ' door ' . $naam : '');

if (!verstuur($onderwerp, implode("\n", $tekst), $naam, $email)) {
    klaar(502, 'Versturen lukte niet. Mail ons gerust rechtstreeks op ' . ONTVANGER . '.');
}

tel_op($teller);
klaar(200, 'Bedankt, je aanvraag is verstuurd.', true);
