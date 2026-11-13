<?php
/**
 * One-time installer: writes the config file, creates the tables, creates the
 * admin account and (optionally) loads sample trips & Hurghada places.
 *
 * Security:
 *  - Nothing happens without the INSTALLATION KEY. The key is generated into a
 *    private file that only the hosting account owner can read (File Manager),
 *    never shown on any page.
 *  - Once a config file or data/installed.lock exists, the installer is
 *    permanently disabled and rejects every request.
 */
define('INSTALLER', true);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/schema.php';

/** Installed (or partly installed): any config file or the lock file disables the installer for good. */
function installer_locked(): bool
{
    return config_path() !== null || is_file(ROOT . '/data/installed.lock');
}

/** PRIVATE_DIR if it exists or can be created and is writable (host allows writing outside public_html). */
function usable_private_dir(): ?string
{
    $dir = PRIVATE_DIR;
    if ((@is_dir($dir) || @mkdir($dir, 0700, true)) && @is_writable($dir)) {
        return $dir;
    }
    return null;
}

/** Existing installation key file, if any. */
function install_key_file(): ?string
{
    if (@is_file(PRIVATE_DIR . '/install.key')) {
        return PRIVATE_DIR . '/install.key';
    }
    $found = glob(ROOT . '/data/install-*.key') ?: [];
    return $found[0] ?? null;
}

/** Creates the installation key file once; returns its path or null if it could not be written. */
function ensure_install_key(): ?string
{
    $file = install_key_file();
    if ($file !== null) {
        return $file;
    }
    $key = bin2hex(random_bytes(24)); // 192-bit secret
    $dir = usable_private_dir();
    // Fallback inside data/ (web access denied by .htaccess) with an unguessable file name.
    $file = $dir !== null ? $dir . '/install.key' : ROOT . '/data/install-' . bin2hex(random_bytes(8)) . '.key';
    if (@file_put_contents($file, $key . "\n", LOCK_EX) === false) {
        return null;
    }
    @chmod($file, 0600);
    return $file;
}

function read_install_key(?string $file): string
{
    return $file !== null ? trim((string)@file_get_contents($file)) : '';
}

$locked = installer_locked();
if ($locked) {
    http_response_code(403);
}

start_session();
$installed = false;
$errors = [];
$keyFile = $locked ? null : ensure_install_key();
$keyInPrivateDir = $keyFile !== null && str_starts_with($keyFile, PRIVATE_DIR);
if (!$locked && $keyFile === null) {
    $errors[] = 'تعذر إنشاء ملف مفتاح التثبيت. تأكد أن مجلد data قابل للكتابة.';
}

$v = [
    'driver' => $_POST['driver'] ?? 'mysql',
    'host'   => trim((string)($_POST['host'] ?? 'localhost')),
    'name'   => trim((string)($_POST['name'] ?? '')),
    'user'   => trim((string)($_POST['user'] ?? '')),
    'admin_user' => trim((string)($_POST['admin_user'] ?? 'admin')),
    'admin_name' => trim((string)($_POST['admin_name'] ?? '')),
    'seed'   => isset($_POST['seed']) || $_SERVER['REQUEST_METHOD'] !== 'POST',
];

if (!$locked && $keyFile !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // 1) Authorisation first: no validation, DB connection or file write happens before the key matches.
    $expected = read_install_key($keyFile);
    $given = trim((string)($_POST['install_key'] ?? ''));
    if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
        usleep(1000000);
        $errors[] = 'مفتاح التثبيت غير صحيح. انسخه من الملف الموضح أعلى النموذج.';
    } else {
        $pass = (string)($_POST['pass'] ?? '');
        $adminPass = (string)($_POST['admin_pass'] ?? '');

        if (!in_array($v['driver'], ['mysql', 'sqlite'], true)) {
            $errors[] = 'نوع قاعدة بيانات غير صالح.';
        }
        if ($v['driver'] === 'mysql' && ($v['name'] === '' || $v['user'] === '')) {
            $errors[] = 'اكتب اسم قاعدة البيانات واسم المستخدم كما أنشأتهما في لوحة Enhance (تبويب Databases).';
        }
        if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $v['admin_user'])) {
            $errors[] = 'اسم مستخدم الأدمن يجب أن يكون 3 أحرف على الأقل (حروف إنجليزية وأرقام).';
        }
        if (mb_strlen($adminPass) < 8) {
            $errors[] = 'كلمة مرور الأدمن يجب ألا تقل عن 8 أحرف.';
        }

        // Config goes outside the web root when possible, otherwise into the site folder (protected by .htaccess).
        $privateDir = usable_private_dir();
        $configFile = ($privateDir ?? ROOT) . '/config.php';
        if (!is_writable(dirname($configFile))) {
            $errors[] = 'لا يمكن إنشاء ملف الإعدادات: المجلد غير قابل للكتابة.';
        }

        if (!$errors) {
            $db = $v['driver'] === 'sqlite'
                ? ['driver' => 'sqlite', 'path' => 'data/eagles.sqlite']
                : ['driver' => 'mysql', 'host' => $v['host'] ?: 'localhost', 'port' => 3306, 'name' => $v['name'], 'user' => $v['user'], 'pass' => $pass];
            $pdo = null;
            $configWritten = false;
            try {
                $pdo = make_pdo($db);
                install_schema($pdo, $db['driver']); // DDL (auto-commits on MySQL), idempotent
                $pdo->beginTransaction();             // admin + sample data are only kept if the config is written too

                $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
                if ($count > 0) {
                    // Never take over an existing database that already has an administrator.
                    throw new RuntimeException('قاعدة البيانات تحتوي على حساب مدير بالفعل. استخدم قاعدة بيانات فارغة.');
                }
                $pdo->prepare('INSERT INTO users (username, full_name, password_hash, created_at) VALUES (?,?,?,?)')
                    ->execute([$v['admin_user'], $v['admin_name'] ?: $v['admin_user'], password_hash($adminPass, PASSWORD_DEFAULT), now()]);
                if ($v['seed'] && (int)$pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn() === 0) {
                    seed_sample_content($pdo);
                }

                $config = "<?php\n// Generated by install.php on " . date('Y-m-d H:i') . ". Keep this file private.\nreturn [\n"
                    . "    'db' => " . var_export($db, true) . ",\n"
                    . "    // اتركه فارغاً ليتم اكتشاف رابط الموقع تلقائياً، أو اكتب مثلاً 'https://eaglestours.com'\n"
                    . "    'site_url' => '',\n"
                    . "    'debug' => false,\n];\n";
                if (file_put_contents($configFile, $config, LOCK_EX) === false) {
                    throw new RuntimeException('تعذر كتابة ملف الإعدادات');
                }
                $configWritten = true;
                @chmod($configFile, $privateDir !== null ? 0600 : 0640);
                $pdo->commit();
                file_put_contents(ROOT . '/data/installed.lock', date('c'));
                @unlink($keyFile); // the key is single-use
                $installed = true;
                $configOutside = $privateDir !== null;
            } catch (Throwable $ex) {
                if ($pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($configWritten && !$installed) {
                    @unlink($configFile);
                }
                $errors[] = $ex instanceof PDOException
                    ? 'تعذر الاتصال بقاعدة البيانات أو إنشاء الجداول: ' . $ex->getMessage()
                    : $ex->getMessage();
            }
        }
    }
}
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>تثبيت موقع Eagles Tourism</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="auth-page">
<div class="auth-card auth-card--wide">
    <div class="auth-brand"><?= brand_mark() ?><div><strong>EAGLES TOURISM</strong><small>معالج التثبيت</small></div></div>

    <?php if ($installed || $locked): ?>
        <?php if ($installed): ?>
            <div class="alert alert-success">تم التثبيت بنجاح! 🎉</div>
            <p class="muted">ملف الإعدادات محفوظ <?= !empty($configOutside) ? 'خارج <code>public_html</code> في مجلد <code>eagles-private</code>' : 'داخل الموقع ومحمي من الوصول المباشر' ?>، ومعالج التثبيت أصبح معطلاً نهائياً.</p>
        <?php else: ?>
            <div class="alert alert-info">الموقع مُثبت بالفعل، ومعالج التثبيت معطل.</div>
        <?php endif; ?>
        <p class="muted">احذف ملف <code>install.php</code> من <code>public_html</code> عبر Files في لوحة Enhance.</p>
        <div class="btn-row">
            <a class="btn btn-primary" href="<?= e(base_url('admin/login.php')) ?>">الدخول إلى لوحة التحكم</a>
            <a class="btn btn-light" href="<?= e(base_url()) ?>">عرض الموقع</a>
        </div>
    <?php else: ?>
        <h1 class="auth-title">إعداد الموقع لأول مرة</h1>
        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

        <form method="post" class="form" autocomplete="off">
            <?= csrf_field() ?>
            <fieldset>
                <legend>0. مفتاح التثبيت</legend>
                <div class="field"><label>مفتاح التثبيت</label><input name="install_key" dir="ltr" required autocomplete="off" spellcheck="false"></div>
                <p class="hint">
                    لحماية الموقع، التثبيت يحتاج مفتاحاً سرياً لا يراه إلا صاحب الاستضافة.
                    افتح Files في لوحة Enhance وانسخ محتوى الملف:
                    <?php if ($keyInPrivateDir): ?>
                        <code dir="ltr">eagles-private/install.key</code> (في المجلد الذي يحتوي على <code>public_html</code>، وليس بداخله).
                    <?php else: ?>
                        داخل مجلد <code dir="ltr">public_html/data/</code> الملف الذي يبدأ اسمه بـ <code dir="ltr">install-</code> وينتهي بـ <code dir="ltr">.key</code>.
                    <?php endif; ?>
                </p>
            </fieldset>
            <fieldset>
                <legend>1. قاعدة البيانات</legend>
                <div class="field">
                    <label>نوع قاعدة البيانات</label>
                    <select name="driver" id="driver">
                        <option value="mysql" <?= $v['driver'] === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB (موصى به على الاستضافة)</option>
                        <option value="sqlite" <?= $v['driver'] === 'sqlite' ? 'selected' : '' ?>>SQLite (بدون إعداد - ملف داخل الموقع)</option>
                    </select>
                </div>
                <div id="mysqlFields">
                    <div class="grid-2">
                        <div class="field"><label>الخادم (Host)</label><input name="host" value="<?= e($v['host']) ?>" dir="ltr"></div>
                        <div class="field"><label>اسم قاعدة البيانات</label><input name="name" value="<?= e($v['name']) ?>" dir="ltr" placeholder="eagles_db"></div>
                        <div class="field"><label>اسم المستخدم</label><input name="user" value="<?= e($v['user']) ?>" dir="ltr" placeholder="eagles_user"></div>
                        <div class="field"><label>كلمة المرور</label><input type="password" name="pass" dir="ltr"></div>
                    </div>
                    <p class="hint">من لوحة Enhance على hosting.com: افتح الموقع ← تبويب Databases ← Create database، وأنشئ قاعدة بيانات ومستخدماً ثم اكتبهم هنا كما تظهر في اللوحة. الخادم غالباً <code>localhost</code>.</p>
                </div>
            </fieldset>
            <fieldset>
                <legend>2. حساب مدير لوحة التحكم</legend>
                <div class="grid-2">
                    <div class="field"><label>اسم المستخدم</label><input name="admin_user" value="<?= e($v['admin_user']) ?>" dir="ltr" required></div>
                    <div class="field"><label>الاسم الظاهر</label><input name="admin_name" value="<?= e($v['admin_name']) ?>" placeholder="مثال: محمد"></div>
                    <div class="field"><label>كلمة المرور (8 أحرف على الأقل)</label><input type="password" name="admin_pass" dir="ltr" required minlength="8"></div>
                </div>
            </fieldset>
            <label class="check"><input type="checkbox" name="seed" <?= $v['seed'] ? 'checked' : '' ?>> إضافة رحلات ومعالم تجريبية (يمكنك تعديلها أو حذفها لاحقاً)</label>
            <button class="btn btn-primary btn-block" type="submit">تثبيت الموقع</button>
        </form>
        <script>
            const d = document.getElementById('driver'), f = document.getElementById('mysqlFields');
            const sync = () => f.style.display = d.value === 'mysql' ? '' : 'none';
            d.addEventListener('change', sync); sync();
        </script>
    <?php endif; ?>
</div>
</body>
</html>
