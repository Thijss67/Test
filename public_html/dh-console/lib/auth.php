<?php
/** Inloggen, sessie en beveiliging tegen misbruik. */

function start_sessie(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $veilig = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    // Het pad volgt de map waarin het paneel staat; hernoem je die, dan
    // blijft de sessie gewoon werken.
    $pad = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => ($pad === '' ? '/' : $pad . '/'),
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => $veilig,
    ]);
    session_name('dhbeheer');
    session_start();
}

function wachtwoord_ingesteld(): bool
{
    return is_file(WACHTWOORD_BESTAND);
}

function bewaar_wachtwoord(string $wachtwoord): void
{
    $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    schrijf_bestand(WACHTWOORD_BESTAND, "<?php\n// Automatisch aangemaakt. Kwijt? Verwijder dit bestand en stel opnieuw in.\nreturn " . var_export($hash, true) . ";\n");
}

function controleer_wachtwoord(string $wachtwoord): bool
{
    if (!wachtwoord_ingesteld()) {
        return false;
    }
    $hash = require WACHTWOORD_BESTAND;
    return is_string($hash) && password_verify($wachtwoord, $hash);
}

function ingelogd(): bool
{
    start_sessie();
    return !empty($_SESSION['ingelogd']) && ($_SESSION['ip'] ?? '') === ($_SERVER['REMOTE_ADDR'] ?? '');
}

function log_in(): void
{
    start_sessie();
    session_regenerate_id(true);
    $_SESSION['ingelogd'] = true;
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
}

function log_uit(): void
{
    start_sessie();
    $_SESSION = [];
    session_destroy();
}

/** Eenvoudige rem op wachtwoord raden, per IP-adres. */
function geblokkeerd(): int
{
    $pogingen = is_file(POGINGEN_BESTAND)
        ? (json_decode((string) file_get_contents(POGINGEN_BESTAND), true) ?: [])
        : [];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'onbekend';
    $rij = $pogingen[$ip] ?? null;
    if (!$rij || ($rij['aantal'] ?? 0) < MAX_POGINGEN) {
        return 0;
    }
    $over = (int) ($rij['laatste'] + BLOKKADE_SECONDEN - time());
    return max(0, $over);
}

function noteer_poging(bool $gelukt): void
{
    $pogingen = is_file(POGINGEN_BESTAND)
        ? (json_decode((string) file_get_contents(POGINGEN_BESTAND), true) ?: [])
        : [];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'onbekend';
    if ($gelukt) {
        unset($pogingen[$ip]);
    } else {
        $aantal = (int) ($pogingen[$ip]['aantal'] ?? 0);
        // na afloop van de blokkade begint de teller opnieuw
        if (isset($pogingen[$ip]['laatste']) && time() - $pogingen[$ip]['laatste'] > BLOKKADE_SECONDEN) {
            $aantal = 0;
        }
        $pogingen[$ip] = ['aantal' => $aantal + 1, 'laatste' => time()];
    }
    // oude rijen opruimen
    foreach ($pogingen as $adres => $rij) {
        if (time() - ($rij['laatste'] ?? 0) > 86400) {
            unset($pogingen[$adres]);
        }
    }
    schrijf_bestand(POGINGEN_BESTAND, json_encode($pogingen));
}

function csrf_token(): string
{
    start_sessie();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_veld(): string
{
    return '<input type="hidden" name="token" value="' . esc(csrf_token()) . '" />';
}

function controleer_csrf(): void
{
    start_sessie();
    $gegeven = (string) ($_POST['token'] ?? '');
    if ($gegeven === '' || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $gegeven)) {
        http_response_code(400);
        exit('Formulier verlopen. Ga terug, ververs de pagina en probeer het opnieuw.');
    }
}
