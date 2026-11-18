<?php
/**
 * Public-site languages.
 *
 * Arabic is the source language: templates call t() with the Arabic text and the
 * other languages translate that Arabic text through includes/lang/<code>.php.
 * Missing translations fall back to the Arabic text. The admin area is Arabic only.
 */

const LANGS = [
    'ar' => ['name' => 'العربية', 'short' => 'AR', 'dir' => 'rtl', 'locale' => 'ar_EG'],
    'en' => ['name' => 'English', 'short' => 'EN', 'dir' => 'ltr', 'locale' => 'en_US'],
    'ru' => ['name' => 'Русский', 'short' => 'RU', 'dir' => 'ltr', 'locale' => 'ru_RU'],
    'es' => ['name' => 'Español', 'short' => 'ES', 'dir' => 'ltr', 'locale' => 'es_ES'],
];
const DEFAULT_LANG = 'ar';

/** Settings whose text is shown to visitors and can be translated per language (stored as "<key>__<lang>"). */
const TRANSLATABLE_SETTINGS = [
    'site_tagline', 'address', 'working_hours', 'currency', 'hero_title', 'hero_subtitle',
    'about_text', 'booking_policy', 'testimonials', 'meta_description',
];

/** Translatable text columns of trips and places (see trip_translations / place_translations). */
const TRIP_I18N_FIELDS  = ['title', 'short_description', 'description', 'duration', 'location', 'pickup_time', 'group_size', 'includes', 'excludes', 'itinerary'];
const PLACE_I18N_FIELDS = ['name', 'description'];
/** Column sizes of the translation tables (longer text is cut). */
const TRIP_TR_LIMITS  = ['title' => 200, 'short_description' => 1000, 'duration' => 100, 'location' => 150, 'pickup_time' => 100, 'group_size' => 100];
const PLACE_TR_LIMITS = ['name' => 150, 'description' => 1000];

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function is_admin_area(): bool
{
    return defined('INSTALLER') || str_contains(str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '')), '/admin/');
}

/** Current language: ?lang= (remembered in a cookie) → cookie → browser language → Arabic. */
function lang(): string
{
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }
    if (PHP_SAPI === 'cli' || is_admin_area()) {
        return $lang = DEFAULT_LANG;
    }
    $q = $_GET['lang'] ?? null;
    if (is_string($q) && isset(LANGS[$q])) {
        if (($_COOKIE['lang'] ?? '') !== $q && !headers_sent()) {
            setcookie('lang', $q, ['expires' => time() + 31536000, 'path' => '/', 'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax']);
        }
        return $lang = $q;
    }
    $c = $_COOKIE['lang'] ?? null;
    if (is_string($c) && isset(LANGS[$c])) {
        return $lang = $c;
    }
    return $lang = browser_lang();
}

/**
 * Picks a language from Accept-Language. No header (most crawlers) or any Arabic → Arabic;
 * otherwise the first supported language, and English for every other foreign language.
 */
function browser_lang(): string
{
    $header = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    if (trim($header) === '') {
        return DEFAULT_LANG;
    }
    $codes = [];
    foreach (explode(',', $header) as $part) {
        $code = substr(trim(explode(';', $part)[0]), 0, 2);
        if ($code !== '') {
            $codes[] = $code;
        }
    }
    if (in_array('ar', $codes, true)) {
        return 'ar';
    }
    foreach ($codes as $code) {
        if (isset(LANGS[$code])) {
            return $code;
        }
    }
    return 'en';
}

function lang_dir(): string
{
    return LANGS[lang()]['dir'];
}

function lang_dict(string $lang): array
{
    static $dicts = [];
    if (!isset($dicts[$lang])) {
        $file = __DIR__ . '/lang/' . basename($lang) . '.php';
        $dicts[$lang] = ($lang !== DEFAULT_LANG && is_file($file)) ? (array)require $file : [];
    }
    return $dicts[$lang];
}

/** Translates Arabic UI text into the current language. {name} placeholders are replaced from $vars. */
function t(string $text, array $vars = []): string
{
    $tr = lang_dict(lang())[$text] ?? $text;
    if (!is_string($tr)) {
        $tr = $text;
    }
    if ($vars) {
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{' . $k . '}'] = (string)$v;
        }
        $tr = strtr($tr, $map);
    }
    return $tr;
}

/** "5 trips" with the right plural form (Russian has three). $unit is 'trip' or 'session'. */
function count_label(int $n, string $unit): string
{
    $forms = lang_dict(lang())['@plural'][$unit] ?? null;
    if (!$forms) {
        return $n . ' ' . ($unit === 'session' ? 'جلسة' : 'رحلة');
    }
    if (lang() === 'ru') {
        $m10 = $n % 10;
        $m100 = $n % 100;
        $i = ($m10 === 1 && $m100 !== 11) ? 0 : (($m10 >= 2 && $m10 <= 4 && ($m100 < 10 || $m100 >= 20)) ? 1 : 2);
    } else {
        $i = $n === 1 ? 0 : 1;
    }
    return $n . ' ' . ($forms[$i] ?? end($forms));
}

/** Built-in translation of a default setting text (used until the admin enters their own). */
function default_setting_translation(string $key, string $lang): ?string
{
    $v = lang_dict($lang)['@settings'][$key] ?? null;
    return is_string($v) ? $v : null;
}

/** Link to a public page that keeps the visitor's language when it differs from the default. */
function url(string $path = ''): string
{
    $u = base_url($path);
    if (lang() === DEFAULT_LANG) {
        return $u;
    }
    $hash = '';
    if (($p = strpos($u, '#')) !== false) {
        $hash = substr($u, $p);
        $u = substr($u, 0, $p);
    }
    return $u . (str_contains($u, '?') ? '&' : '?') . 'lang=' . lang() . $hash;
}

/** The current page in another language (relative URL, or absolute when $absolute). */
function lang_url(string $code, bool $absolute = false): string
{
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)parse_url($uri, PHP_URL_PATH);
    parse_str((string)parse_url($uri, PHP_URL_QUERY), $query);
    unset($query['lang'], $query['booked'], $query['sent']);
    $query['lang'] = $code;
    $u = $path . '?' . http_build_query($query);
    if ($absolute) {
        $host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        $u = (is_https() ? 'https://' : 'http://') . $host . $u;
    }
    return $u;
}

/* ------------------------------------------------- translated content */

/** Overlays the current language's translations on trip rows (empty translations keep the Arabic text). */
function localize_trips(array $rows): array
{
    return localize_rows($rows, 'trip_translations', 'trip_id', TRIP_I18N_FIELDS);
}

function localize_trip(array $row): array
{
    return localize_trips([$row])[0];
}

function localize_places(array $rows): array
{
    return localize_rows($rows, 'place_translations', 'place_id', PLACE_I18N_FIELDS);
}

function localize_rows(array $rows, string $table, string $fk, array $fields): array
{
    if (lang() === DEFAULT_LANG || !$rows) {
        return $rows;
    }
    $ids = array_values(array_unique(array_map(fn($r) => (int)$r['id'], $rows)));
    try {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $st = db()->prepare("SELECT * FROM $table WHERE lang = ? AND $fk IN ($ph)");
        $st->execute([lang(), ...$ids]);
        $tr = [];
        foreach ($st as $row) {
            $tr[(int)$row[$fk]] = $row;
        }
    } catch (PDOException) {
        return $rows; // table not created yet
    }
    foreach ($rows as &$r) {
        $t = $tr[(int)$r['id']] ?? null;
        if ($t) {
            foreach ($fields as $f) {
                if (trim((string)($t[$f] ?? '')) !== '') {
                    $r[$f] = $t[$f];
                }
            }
        }
    }
    return $rows;
}

/** All saved translations of one trip/place, keyed by language (used by the admin forms). */
function load_translations(string $table, string $fk, int $id): array
{
    $out = [];
    if ($id <= 0) {
        return $out;
    }
    try {
        $st = db()->prepare("SELECT * FROM $table WHERE $fk = ?");
        $st->execute([$id]);
        foreach ($st as $row) {
            $out[$row['lang']] = $row;
        }
    } catch (PDOException) {
    }
    return $out;
}

/** Replaces the translations of one trip/place with the posted values ($posted[lang][field]). */
function save_translations(string $table, string $fk, int $id, array $fields, array $posted, array $limits = []): void
{
    $pdo = db();
    $pdo->prepare("DELETE FROM $table WHERE $fk = ?")->execute([$id]);
    $cols = implode(', ', $fields);
    $ph = implode(', ', array_fill(0, count($fields) + 2, '?'));
    $ins = $pdo->prepare("INSERT INTO $table ($fk, lang, $cols) VALUES ($ph)");
    foreach (array_keys(LANGS) as $code) {
        if ($code === DEFAULT_LANG) {
            continue;
        }
        $vals = [];
        $any = false;
        foreach ($fields as $f) {
            $v = trim((string)($posted[$code][$f] ?? ''));
            if (isset($limits[$f])) {
                $v = mb_substr($v, 0, $limits[$f]);
            }
            $any = $any || $v !== '';
            $vals[] = $v;
        }
        if ($any) {
            $ins->execute([$id, $code, ...$vals]);
        }
    }
}
