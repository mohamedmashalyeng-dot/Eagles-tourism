<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow');
start_session();

if (current_admin()) {
    redirect('admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? [];
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], fn($t) => $t > time() - 900);

    if (count($_SESSION['login_attempts']) >= 5) {
        $error = 'محاولات كثيرة خاطئة. حاول مرة أخرى بعد 15 دقيقة.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([$username]);
        $user = $st->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$user['id'];
            unset($_SESSION['login_attempts']);
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
            redirect('admin/index.php');
        }
        $_SESSION['login_attempts'][] = time();
        usleep(600000);
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تسجيل الدخول | لوحة التحكم</title>
    <link rel="icon" href="<?= e(base_url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-brand"><?= brand_mark() ?><div><strong>EAGLES TOURISM</strong><small>لوحة التحكم</small></div></div>
    <h1 class="auth-title">تسجيل الدخول</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?= render_flashes() ?>
    <form method="post" class="form">
        <?= csrf_field() ?>
        <div class="field"><label for="u">اسم المستخدم</label><input id="u" name="username" dir="ltr" required autofocus autocomplete="username"></div>
        <div class="field"><label for="p">كلمة المرور</label><input id="p" type="password" name="password" dir="ltr" required autocomplete="current-password"></div>
        <button class="btn btn-primary btn-block" type="submit"><?= icon('lock') ?> دخول</button>
    </form>
    <a class="back-link" href="<?= e(base_url()) ?>">← العودة إلى الموقع</a>
</div>
</body>
</html>
