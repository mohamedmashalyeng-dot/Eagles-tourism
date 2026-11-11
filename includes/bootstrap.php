<?php
/**
 * Eagles Tourism - core bootstrap
 * Loads config, opens the database, starts the session and defines helpers.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP_VERSION', '1.0.0');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Cairo');

const TRIP_CATEGORIES = [
    'sea'    => 'رحلات بحرية',
    'safari' => 'سفاري الصحراء',
    'diving' => 'غوص وسنوركل',
    'day'    => 'رحلات اليوم الواحد',
    'city'   => 'جولات المدينة',
    'family' => 'عائلية ومغامرات',
];

const BOOKING_STATUSES = [
    'new'       => 'جديد',
    'contacted' => 'تم التواصل',
    'confirmed' => 'مؤكد',
    'cancelled' => 'ملغي',
];

/* ---------------------------------------------------------------- URLs */

function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $cfg = config('site_url');
        if (is_string($cfg) && $cfg !== '') {
            $base = rtrim($cfg, '/');
        } else {
            $dir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $dir  = preg_replace('#/admin$#', '', $dir);
            $base = rtrim((string)$dir, '/');
        }
    }
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? filemtime($file) : APP_VERSION;
    return base_url('assets/' . $path) . '?v=' . $v;
}

/** Returns a usable src for a stored image (uploaded path or external URL). */
function img_src(?string $path): string
{
    $path = trim((string)$path);
    if ($path === '') {
        return base_url('assets/img/placeholder.svg');
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return base_url($path);
}

function redirect(string $to): never
{
    if (!preg_match('#^(https?:)?//#', $to) && !str_starts_with($to, '/')) {
        $to = base_url($to);
    }
    header('Location: ' . $to);
    exit;
}

/* -------------------------------------------------------------- config */

function config(?string $key = null, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $file = ROOT . '/config.php';
        $cfg = is_file($file) ? (array)require $file : [];
    }
    if ($key === null) {
        return $cfg;
    }
    return $cfg[$key] ?? $default;
}

function is_installed(): bool
{
    return is_file(ROOT . '/config.php') && is_file(ROOT . '/data/installed.lock');
}

/* ------------------------------------------------------------ database */

function make_pdo(array $c): PDO
{
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (($c['driver'] ?? 'mysql') === 'sqlite') {
        $path = $c['path'] ?? (ROOT . '/data/eagles.sqlite');
        $pdo = new PDO('sqlite:' . $path, null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    }
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $c['host'] ?? 'localhost',
        (int)($c['port'] ?? 3306),
        $c['name'] ?? ''
    );
    return new PDO($dsn, $c['user'] ?? '', $c['pass'] ?? '', $opts);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = make_pdo((array)config('db', []));
    }
    return $pdo;
}

function db_driver(): string
{
    return (string)(config('db', [])['driver'] ?? 'mysql');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/* ------------------------------------------------------------ settings */

const DEFAULT_SETTINGS = [
    'site_name'      => 'Eagles Tourism',
    'site_tagline'   => 'رحلات وسياحة الغردقة',
    'phone'          => '+20 100 000 0000',
    'whatsapp'       => '201000000000',
    'email'          => 'info@eagles-tourism.com',
    'address'        => 'شارع الشيراتون، السقالة، الغردقة، البحر الأحمر، مصر',
    'facebook'       => '',
    'instagram'      => '',
    'currency'       => 'ج.م',
    'hero_title'     => 'اكتشف سحر الغردقة مع إيجلز',
    'hero_subtitle'  => 'رحلات بحرية، سفاري، غوص، وجولات لا تُنسى على ساحل البحر الأحمر — بتنظيم احترافي وأسعار واضحة.',
    'hero_image'     => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=1920&q=80',
    'about_text'     => 'إيجلز للسياحة شركة متخصصة في تنظيم الرحلات السياحية بمدينة الغردقة والبحر الأحمر. نؤمن أن كل رحلة يجب أن تكون تجربة آمنة ومريحة وممتعة، لذلك نختار أفضل اليخوت والمرشدين ونهتم بأدق التفاصيل من لحظة الحجز وحتى عودتك إلى فندقك.',
    'working_hours'  => 'يومياً من 8 صباحاً حتى 11 مساءً',
    'map_query'      => 'Hurghada, Red Sea, Egypt',
    'booking_policy' => "الدفع عند تأكيد الحجز\nإلغاء مجاني قبل الرحلة بـ 24 ساعة",
    'stat_clients'   => '5000+',
    'stat_rating'    => '4.9',
    'stat_years'     => '10',
    'testimonials'   => "أحمد م. | القاهرة | رحلة أورانج باي كانت رائعة، التنظيم ممتاز والمواعيد مضبوطة والأكل جميل جداً.\nسارة ك. | الإسكندرية | السفاري كانت تجربة مختلفة تماماً، الأطفال استمتعوا جداً بالجمال والعرض.\nDaniel R. | Germany | Great diving experience for beginners. The instructor was patient and professional.",
    'meta_description' => 'إيجلز للسياحة - أفضل الرحلات البحرية والسفاري والغوص في الغردقة بأسعار مميزة وخدمة احترافية.',
];

function settings(): array
{
    static $s = null;
    if ($s === null) {
        $s = DEFAULT_SETTINGS;
        try {
            foreach (db()->query('SELECT skey, svalue FROM settings') as $row) {
                $s[$row['skey']] = (string)$row['svalue'];
            }
        } catch (Throwable) {
            // table might not exist yet (during install)
        }
    }
    return $s;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    $pdo = db();
    $st = $pdo->prepare('UPDATE settings SET svalue = ? WHERE skey = ?');
    $st->execute([$value, $key]);
    if ($st->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE skey = ?');
        $exists->execute([$key]);
        if ((int)$exists->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?)')->execute([$key, $value]);
        }
    }
}

/* ------------------------------------------------------------- output */

function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function price(float|int|string|null $amount): string
{
    $amount = (float)$amount;
    $formatted = number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    return $formatted . ' ' . setting('currency', 'ج.م');
}

function lines(?string $text): array
{
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', (string)$text) as $l) {
        $l = trim($l);
        if ($l !== '') {
            $out[] = $l;
        }
    }
    return $out;
}

function excerpt(?string $text, int $len = 120): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$text)));
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}

function whatsapp_link(string $message = ''): string
{
    $num = preg_replace('/\D+/', '', setting('whatsapp'));
    return 'https://wa.me/' . $num . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

function category_label(?string $key): string
{
    return TRIP_CATEGORIES[$key] ?? 'رحلات متنوعة';
}

/* -------------------------------------------------------------- session */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('eagles_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string)($_POST['_token'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('انتهت صلاحية الجلسة، برجاء الرجوع وإعادة المحاولة.');
    }
}

function flash(string $type, string $message): void
{
    start_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function render_flashes(): string
{
    start_session();
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="alert alert-' . e($f['type']) . '" role="status">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

/** Remember submitted form values across a redirect. */
function old(string $key, string $default = ''): string
{
    start_session();
    return (string)($_SESSION['old'][$key] ?? $default);
}

/* --------------------------------------------------------------- auth */

function current_admin(): ?array
{
    start_session();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $user = false;
    if ($user === false) {
        $st = db()->prepare('SELECT id, username, full_name FROM users WHERE id = ?');
        $st->execute([$_SESSION['admin_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

function require_admin(): array
{
    $u = current_admin();
    if (!$u) {
        redirect('admin/login.php');
    }
    return $u;
}

/* ------------------------------------------------------------ uploads */

/** Normalises a multi-file $_FILES entry into a list of single-file arrays. */
function normalize_files(?array $f): array
{
    if (!$f || !isset($f['name'])) {
        return [];
    }
    if (!is_array($f['name'])) {
        return [$f];
    }
    $out = [];
    foreach ($f['name'] as $i => $name) {
        $out[] = [
            'name'     => $name,
            'type'     => $f['type'][$i] ?? '',
            'tmp_name' => $f['tmp_name'][$i] ?? '',
            'error'    => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $f['size'][$i] ?? 0,
        ];
    }
    return $out;
}

/**
 * Validates and stores an uploaded image. Returns the relative path
 * (e.g. "uploads/trips/2026...jpg") or null when no file was sent.
 */
function upload_image(?array $file, string $subdir): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('تعذر رفع الصورة "' . ($file['name'] ?? '') . '" (قد يكون حجمها أكبر من المسموح على الاستضافة).');
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('حجم الصورة يجب ألا يزيد عن 10 ميجابايت.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('ملف غير صالح.');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('الملف "' . ($file['name'] ?? '') . '" ليس صورة مدعومة (JPG, PNG, WEBP, GIF).');
    }
    $subdir = preg_replace('/[^a-z0-9_-]/i', '', $subdir);
    $dir = ROOT . '/uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('تعذر إنشاء مجلد الرفع، تأكد من صلاحيات مجلد uploads.');
    }
    $ext = $types[$info[2]];
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!resize_image($file['tmp_name'], $dest, $info, 1920)) {
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('تعذر حفظ الصورة على الخادم.');
        }
    }
    @chmod($dest, 0644);
    return 'uploads/' . $subdir . '/' . $name;
}

/** Downscales large JPG/PNG/WEBP images with GD. Returns false if not handled. */
function resize_image(string $src, string $dest, array $info, int $max): bool
{
    [$w, $h, $type] = $info;
    if (!function_exists('imagecreatetruecolor') || max($w, $h) <= $max) {
        return false;
    }
    $loaders = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!isset($loaders[$type]) || !function_exists($loaders[$type])) {
        return false;
    }
    $img = @$loaders[$type]($src);
    if (!$img) {
        return false;
    }
    $ratio = $max / max($w, $h);
    $nw = (int)round($w * $ratio);
    $nh = (int)round($h * $ratio);
    $out = imagecreatetruecolor($nw, $nh);
    if ($type !== IMAGETYPE_JPEG) {
        imagealphablending($out, false);
        imagesavealpha($out, true);
    }
    imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($out, $dest, 85),
        IMAGETYPE_PNG  => imagepng($out, $dest, 6),
        IMAGETYPE_WEBP => imagewebp($out, $dest, 85),
    };
    imagedestroy($img);
    imagedestroy($out);
    return $ok;
}

/** Removes a previously uploaded file (only inside /uploads). */
function delete_upload(?string $path): void
{
    $path = (string)$path;
    if (!str_starts_with($path, 'uploads/')) {
        return;
    }
    $full = realpath(ROOT . '/' . $path);
    $base = realpath(ROOT . '/uploads');
    if ($full && $base && str_starts_with($full, $base . DIRECTORY_SEPARATOR) && is_file($full)) {
        @unlink($full);
    }
}

/* ------------------------------------------------------------- guards */

if (PHP_SAPI !== 'cli' && !defined('INSTALLER') && !is_installed()) {
    redirect('install.php');
}

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/icons.php';
require __DIR__ . '/partials.php';
