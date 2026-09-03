<?php
/** Kleine hulpjes die overal gebruikt worden. */

/** Alles wat de browser in ziet, gaat hier doorheen. */
function esc(string $tekst): string
{
    return htmlspecialchars($tekst, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Maakt van "Bakkerij De Vries" -> "bakkerij-de-vries". */
function maak_slug(string $tekst): string
{
    $tekst = mb_strtolower(trim($tekst), 'UTF-8');
    $vervang = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
                'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
                'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ç'=>'c','ñ'=>'n','ij'=>'ij','&'=>'en'];
    $tekst = strtr($tekst, $vervang);
    $tekst = preg_replace('/[^a-z0-9]+/', '-', $tekst);
    return trim($tekst, '-');
}

/** Alleen deze vorm van slug laten we toe; houdt paden veilig. */
function geldige_slug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9](?:[a-z0-9-]{0,58}[a-z0-9])?$/', $slug);
}

/** Schrijft eerst naar een tijdelijk bestand en hernoemt daarna: nooit een half bestand. */
function schrijf_bestand(string $pad, string $inhoud): void
{
    $map = dirname($pad);
    if (!is_dir($map) && !mkdir($map, 0755, true) && !is_dir($map)) {
        throw new RuntimeException('Kan de map ' . $map . ' niet aanmaken.');
    }
    $tijdelijk = $pad . '.tmp' . bin2hex(random_bytes(4));
    if (file_put_contents($tijdelijk, $inhoud, LOCK_EX) === false) {
        throw new RuntimeException('Kan niet schrijven naar ' . $pad . '. Controleer de rechten op de map.');
    }
    if (!rename($tijdelijk, $pad)) {
        @unlink($tijdelijk);
        throw new RuntimeException('Kan ' . $pad . ' niet vervangen.');
    }
}

function lees_cases(): array
{
    if (!is_file(CASES_BESTAND)) {
        return [];
    }
    $data = json_decode((string) file_get_contents(CASES_BESTAND), true);
    return is_array($data) && isset($data['cases']) && is_array($data['cases']) ? $data['cases'] : [];
}

function bewaar_cases(array $cases): void
{
    $json = json_encode(['cases' => array_values($cases)],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    schrijf_bestand(CASES_BESTAND, $json . "\n");
}

function zoek_case(array $cases, string $slug): ?int
{
    foreach ($cases as $i => $case) {
        if (($case['slug'] ?? '') === $slug) {
            return $i;
        }
    }
    return null;
}

/**
 * Zet losse tekst om naar veilige HTML. Toegestaan blijven:
 *   **vet**            -> <b>vet</b>
 *   [tekst](https://…) -> <a href="…">tekst</a>
 * Al het andere wordt geescaped, dus er kan geen HTML uit een formulier lekken.
 */
function tekst_naar_html(string $tekst): string
{
    $veilig = esc($tekst);
    $veilig = preg_replace_callback(
        '/\[([^\]]{1,120})\]\((https?:\/\/[^\s)]{1,300})\)/',
        static function (array $m): string {
            $extern = str_starts_with($m[2], BASIS_URL) ? '' : ' target="_blank" rel="noopener"';
            return '<a href="' . $m[2] . '"' . $extern . '>' . $m[1] . '</a>';
        },
        $veilig
    );
    return preg_replace('/\*\*(.+?)\*\*/s', '<b>$1</b>', $veilig);
}
