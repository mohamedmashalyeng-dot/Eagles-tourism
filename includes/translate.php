<?php
/**
 * Automatic translation of admin content (Arabic → en / de / ru / es) with the
 * Google Cloud Translation API v2 (Basic). Only empty translation fields are
 * filled, unless the admin asks to translate everything again.
 *
 * The API key is saved in Settings (or 'google_translate_key' in config.php, which wins).
 */

const TRANSLATE_ENDPOINT = 'https://translation.googleapis.com/language/translate/v2';

function translate_api_key(): string
{
    $key = trim((string)config('google_translate_key', ''));
    return $key !== '' ? $key : trim((string)(settings()['google_translate_key'] ?? ''));
}

function auto_translate_enabled(): bool
{
    return translate_api_key() !== '' && (settings()['auto_translate'] ?? '1') === '1';
}

/**
 * Translates a list of texts in one request (max 128 per request, chunked here).
 * @return string[] same order and keys as $texts
 * @throws RuntimeException with an Arabic message when the API call fails
 */
function google_translate(array $texts, string $target, string $source = DEFAULT_LANG): array
{
    $key = translate_api_key();
    if ($key === '') {
        throw new RuntimeException('مفتاح Google Translate مش متسجل في الإعدادات.');
    }
    $keys = array_keys($texts);
    $values = array_values($texts);
    $out = [];
    foreach (array_chunk($values, 100) as $chunk) {
        $body = http_build_query(['source' => $source, 'target' => $target, 'format' => 'text']);
        foreach ($chunk as $t) {
            $body .= '&q=' . rawurlencode((string)$t);
        }
        $endpoint = (string)config('google_translate_endpoint', TRANSLATE_ENDPOINT); // overridable for local testing
        [$status, $resp] = translate_http_post($endpoint, $body, [
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'X-Goog-Api-Key: ' . $key, // in a header, so the key never appears in URLs or logs
        ]);
        $json = json_decode($resp, true);
        $list = $json['data']['translations'] ?? null;
        if ($status !== 200 || !is_array($list) || count($list) !== count($chunk)) {
            $msg = is_array($json) ? (string)($json['error']['message'] ?? '') : '';
            throw new RuntimeException('Google Translate رفض الطلب (HTTP ' . $status . ')' . ($msg !== '' ? ': ' . $msg : '.'));
        }
        foreach ($list as $tr) {
            $out[] = (string)($tr['translatedText'] ?? '');
        }
    }
    return array_combine($keys, $out);
}

/** @return array{0:int,1:string} [HTTP status, body] */
function translate_http_post(string $url, string $body, array $headers): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            throw new RuntimeException('تعذر الاتصال بخدمة Google Translate: ' . curl_error($ch));
        }
        return [(int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE), (string)$resp];
    }
    $ctx = stream_context_create(['http' => [
        'method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $body,
        'timeout' => 30, 'ignore_errors' => true,
    ]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        throw new RuntimeException('تعذر الاتصال بخدمة Google Translate.');
    }
    preg_match('#^HTTP/\S+\s+(\d{3})#', $http_response_header[0] ?? '', $m);
    return [(int)($m[1] ?? 0), $resp];
}

/**
 * Translates several fields into one language, line by line so lists keep one item per line.
 * A leading time such as "08:00 - " in itineraries is kept as-is and only the text is translated.
 * @param array $fields field => Arabic text
 * @return array field => translation
 */
function translate_fields(array $fields, string $target): array
{
    $parts = [];   // [field, line index, prefix]
    $texts = [];
    $result = [];
    foreach ($fields as $field => $text) {
        $lines = preg_split('/\r\n|\r|\n/', (string)$text);
        $result[$field] = $lines;
        foreach ($lines as $i => $line) {
            $prefix = '';
            $body = $line;
            if (preg_match('/^(\s*[0-9]{1,2}[:.][0-9]{2}\s*[-–—]\s*)(.+)$/u', $line, $m)) {
                [$prefix, $body] = [$m[1], $m[2]];
            }
            if (trim($body) === '' || !preg_match('/\p{Arabic}/u', $body)) {
                continue; // nothing to translate (empty, numbers, names in Latin letters...)
            }
            $parts[] = [$field, $i, $prefix];
            $texts[] = trim($body);
        }
    }
    if ($texts) {
        foreach (google_translate($texts, $target) as $n => $tr) {
            [$field, $i, $prefix] = $parts[$n];
            $result[$field][$i] = $prefix . $tr;
        }
    }
    return array_map(fn($lines) => implode("\n", $lines), $result);
}

/**
 * Fills the missing translations of one trip/place from its Arabic text.
 * @param array $source field => Arabic text
 * @return int number of fields translated
 */
function auto_translate_row(string $table, string $fk, int $id, array $fields, array $source, bool $overwrite = false, array $limits = []): int
{
    $existing = load_translations($table, $fk, $id);
    $count = 0;
    foreach (array_keys(LANGS) as $code) {
        if ($code === DEFAULT_LANG) {
            continue;
        }
        $row = [];
        foreach ($fields as $f) {
            $row[$f] = (string)($existing[$code][$f] ?? '');
        }
        $todo = [];
        foreach ($fields as $f) {
            $ar = trim((string)($source[$f] ?? ''));
            if ($ar !== '' && ($overwrite || trim($row[$f]) === '')) {
                $todo[$f] = $ar;
            }
        }
        if (!$todo) {
            continue;
        }
        foreach (translate_fields($todo, $code) as $f => $tr) {
            $row[$f] = isset($limits[$f]) ? mb_substr($tr, 0, $limits[$f]) : $tr;
            $count++;
        }
        $pdo = db();
        $pdo->prepare("DELETE FROM $table WHERE $fk = ? AND lang = ?")->execute([$id, $code]);
        $cols = implode(', ', $fields);
        $pdo->prepare("INSERT INTO $table ($fk, lang, $cols) VALUES (" . implode(', ', array_fill(0, count($fields) + 2, '?')) . ')')
            ->execute([$id, $code, ...array_values($row)]);
    }
    return $count;
}

/**
 * Fills the missing translations of the visitor-facing settings.
 * Settings still at their default Arabic text are skipped: they already have built-in translations.
 * @param array $arabic key => Arabic value   @param array $current [lang][key] => saved translation
 */
function auto_translate_settings(array $arabic, array $current, bool $overwrite = false): int
{
    $count = 0;
    foreach (array_keys(LANGS) as $code) {
        if ($code === DEFAULT_LANG) {
            continue;
        }
        $todo = [];
        foreach (TRANSLATABLE_SETTINGS as $key) {
            $ar = trim((string)($arabic[$key] ?? ''));
            if ($ar === '' || $ar === (DEFAULT_SETTINGS[$key] ?? null)) {
                continue;
            }
            if ($overwrite || trim((string)($current[$code][$key] ?? '')) === '') {
                $todo[$key] = $ar;
            }
        }
        if ($todo) {
            foreach (translate_fields($todo, $code) as $key => $tr) {
                save_setting($key . '__' . $code, $tr);
                $count++;
            }
        }
    }
    return $count;
}

/** Translates the empty fields of every trip, place and setting. Returns the number of fields translated. */
function auto_translate_everything(): int
{
    @set_time_limit(300);
    $pdo = db();
    $count = 0;
    foreach ($pdo->query('SELECT * FROM trips ORDER BY id')->fetchAll() as $t) {
        $count += auto_translate_row('trip_translations', 'trip_id', (int)$t['id'], TRIP_I18N_FIELDS, $t, false, TRIP_TR_LIMITS);
    }
    foreach ($pdo->query('SELECT * FROM places ORDER BY id')->fetchAll() as $p) {
        $count += auto_translate_row('place_translations', 'place_id', (int)$p['id'], PLACE_I18N_FIELDS, $p, false, PLACE_TR_LIMITS);
    }
    $s = settings();
    $current = [];
    foreach (array_keys(LANGS) as $code) {
        foreach (TRANSLATABLE_SETTINGS as $key) {
            $current[$code][$key] = $s[$key . '__' . $code] ?? '';
        }
    }
    return $count + auto_translate_settings($s, $current);
}

/** Runs $fn and turns the result into an admin flash message. Never throws: the content is already saved. */
function auto_translate_flash(callable $fn): void
{
    try {
        $n = $fn();
        if ($n > 0) {
            flash('success', 'اتترجم تلقائياً ' . $n . ' خانة (إنجليزي / ألماني / روسي / إسباني).');
        }
    } catch (Throwable $ex) {
        error_log('Auto translation failed: ' . $ex->getMessage());
        flash('error', 'اتحفظ، بس الترجمة التلقائية فشلت: ' . $ex->getMessage());
    }
}
