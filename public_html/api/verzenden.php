<?php
/**
 * Gedeelde afhandeling voor de twee formulieren op de site: /contact en
 * /website-concept. Beide nemen een aanvraag aan en mailen die naar DH
 * Studio; alleen de velden verschillen.
 *
 * Staat hier één keer zodat de spamrem en de mailkoppen niet op twee
 * plekken uit elkaar kunnen gaan lopen.
 */
declare(strict_types=1);

const ONTVANGER    = 'info@dhstudio.nl';
const AFZENDER     = 'website@dhstudio.nl';  // moet op het eigen domein staan, anders weigert SPF de mail
const MAX_PER_UUR  = 5;                       // per IP-adres, per formulier
const MIN_SECONDEN = 3;                       // sneller ingevuld dan dit is geen mens

/** Zet de vaste antwoordkoppen. Aanroepen vóór er iets wordt uitgevoerd. */
function begin(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        klaar(405, 'Alleen POST.');
    }
}

/** Stopt met een nette JSON-melding. */
function klaar(int $code, string $melding, bool $gelukt = false): never
{
    http_response_code($code);
    echo json_encode(['ok' => $gelukt, 'melding' => $melding], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Hoeveel aanvragen kwamen er dit uur van dit IP-adres? Geeft de teller
 * terug; die geef je na een geslaagde verzending mee aan tel_op().
 */
function rem(string $formulier): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'onbekend');
    $pad = sys_get_temp_dir() . '/dh-' . $formulier . '-' . sha1($ip . date('YmdH')) . '.tel';
    $aantal = is_file($pad) ? (int) file_get_contents($pad) : 0;
    if ($aantal >= MAX_PER_UUR) {
        klaar(429, 'Je hebt net al een aanvraag gestuurd. Mail ons gerust rechtstreeks op ' . ONTVANGER . '.');
    }
    return $pad;
}

function tel_op(string $pad): void
{
    $aantal = is_file($pad) ? (int) file_get_contents($pad) : 0;
    @file_put_contents($pad, (string) ($aantal + 1), LOCK_EX);
}

/**
 * Haalt regeleindes uit een waarde die op één regel hoort. Zonder dit kan
 * iemand er een extra mailkopregel (denk aan Bcc) doorheen smokkelen.
 */
function een_regel(string $waarde, int $max): string
{
    return mb_substr(trim(str_replace(["\r", "\n"], ' ', $waarde)), 0, $max);
}

/**
 * Verstuurt de mail. $antwoordNaam en $antwoordAdres bepalen het Reply-To,
 * zodat je direct op de aanvrager kunt antwoorden.
 *
 * From blijft bewust het eigen domein: een From op het adres van de
 * bezoeker laat de mail bij veel providers in de spambox belanden.
 */
function verstuur(string $onderwerp, string $tekst, string $antwoordNaam, string $antwoordAdres): bool
{
    $kop = [
        'From: DH Studio website <' . AFZENDER . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
        'X-Mailer: dhstudio.nl',
    ];
    if ($antwoordAdres !== '' && filter_var($antwoordAdres, FILTER_VALIDATE_EMAIL)) {
        array_splice($kop, 1, 0, [
            'Reply-To: ' . mb_encode_mimeheader(een_regel($antwoordNaam, 100), 'UTF-8') . ' <' . $antwoordAdres . '>',
        ]);
    }

    return (bool) @mail(
        ONTVANGER,
        mb_encode_mimeheader($onderwerp, 'UTF-8'),
        $tekst,
        implode("\r\n", $kop),
        '-f' . AFZENDER
    );
}
