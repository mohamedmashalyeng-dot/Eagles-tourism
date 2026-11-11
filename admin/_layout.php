<?php
/** Shared admin layout helpers. */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow');

function admin_header(string $title, string $active = ''): void
{
    $user = require_admin();
    $newCount = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn();
    $menu = [
        'dashboard' => ['الرئيسية', 'index.php', 'grid'],
        'trips'     => ['الرحلات', 'trips.php', 'compass'],
        'places'    => ['معالم الغردقة', 'places.php', 'image'],
        'bookings'  => ['الحجوزات والرسائل', 'bookings.php', 'inbox'],
        'settings'  => ['الإعدادات', 'settings.php', 'sliders'],
    ];
    ?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | لوحة التحكم - <?= e(setting('site_name')) ?></title>
    <link rel="icon" href="<?= e(base_url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<aside class="sidebar" id="sidebar">
    <a class="sidebar__brand" href="<?= e(base_url('admin/index.php')) ?>"><?= brand_mark() ?><div><strong>EAGLES</strong><small>لوحة التحكم</small></div></a>
    <nav class="sidebar__nav">
        <?php foreach ($menu as $key => [$label, $href, $ic]): ?>
            <a href="<?= e(base_url('admin/' . $href)) ?>" class="<?= $active === $key ? 'is-active' : '' ?>">
                <?= icon($ic) ?><span><?= e($label) ?></span>
                <?php if ($key === 'bookings' && $newCount > 0): ?><em class="badge"><?= $newCount ?></em><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar__foot">
        <a href="<?= e(base_url()) ?>" target="_blank"><?= icon('external') ?><span>عرض الموقع</span></a>
        <a href="<?= e(base_url('admin/logout.php')) ?>"><?= icon('logout') ?><span>تسجيل الخروج</span></a>
    </div>
</aside>
<div class="main">
    <header class="topbar">
        <button class="icon-btn menu-btn" onclick="document.getElementById('sidebar').classList.toggle('is-open')" aria-label="القائمة"><?= icon('menu') ?></button>
        <h1><?= e($title) ?></h1>
        <div class="topbar__user"><span class="avatar"><?= e(mb_substr($user['full_name'] ?: $user['username'], 0, 1)) ?></span><span class="hide-sm">أهلاً، <?= e($user['full_name'] ?: $user['username']) ?></span></div>
    </header>
    <div class="content">
        <?= render_flashes() ?>
<?php
}

function admin_footer(): void
{
    ?>
    </div>
</div>
<script>
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (e) { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
});
document.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
        var box = document.getElementById(inp.dataset.preview);
        if (!box) return;
        box.innerHTML = '';
        Array.prototype.forEach.call(inp.files, function (f) {
            if (!f.type.match(/^image\//)) return;
            var img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            box.appendChild(img);
        });
    });
});
</script>
</body>
</html>
<?php
}
