<?php
/** Shared admin layout helpers. */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/translate.php';
header('X-Robots-Tag: noindex, nofollow');

/**
 * Collapsible per-language fields (English / Russian / Spanish) posted as tr[lang][field].
 * $labels: field => [label, type] with type input | short | long | list.
 */
function translations_panel(string $title, array $labels, array $values, bool $compact = false, string $hint = ''): string
{
    ob_start(); ?>
    <<?= $compact ? 'div' : 'section' ?> class="<?= $compact ? 'tr-compact' : 'panel' ?>">
        <?php if ($title !== ''): ?><h2 class="panel__title"><?= e($title) ?> <small>(اختياري — الخانة الفاضية بتعرض النص العربي)</small></h2><?php endif; ?>
        <?php if ($hint !== ''): ?><p class="hint"><?= e($hint) ?></p><?php endif; ?>
        <?php if (auto_translate_enabled()): ?>
            <p class="tr-auto is-on"><?= icon('check') ?> الترجمة التلقائية شغّالة: أي خانة فاضية هتتترجم لوحدها من النص العربي لما تحفظ.</p>
            <label class="check"><input type="checkbox" name="retranslate"> أعد ترجمة كل الخانات من النص العربي (هيستبدل الترجمة الحالية)</label>
        <?php else: ?>
            <p class="tr-auto">الترجمة التلقائية مقفولة. تقدر تفعّلها من <a href="<?= e(base_url('admin/settings.php#auto-translate')) ?>">الإعدادات</a>.</p>
        <?php endif; ?>
        <?php foreach (LANGS as $code => $info): if ($code === DEFAULT_LANG) continue;
            $v = (array)($values[$code] ?? []);
            $filled = count(array_filter(array_map(fn($f) => trim((string)($v[$f] ?? '')), array_keys($labels)), fn($x) => $x !== ''));
        ?>
            <details class="tr-lang">
                <summary>
                    <strong><?= e($info['name']) ?></strong>
                    <em class="tr-state <?= $filled ? 'is-done' : '' ?>"><?= $filled ? 'مترجم ' . $filled . '/' . count($labels) : 'غير مترجم' ?></em>
                </summary>
                <div class="tr-fields" dir="ltr" lang="<?= e($code) ?>">
                    <?php foreach ($labels as $field => [$label, $type]):
                        $name = 'tr[' . $code . '][' . $field . ']';
                        $val = (string)($v[$field] ?? ''); ?>
                        <div class="field <?= $type === 'input' ? '' : 'span-2' ?>">
                            <label dir="rtl"><?= e($label) ?></label>
                            <?php if ($type === 'input'): ?>
                                <input name="<?= e($name) ?>" value="<?= e($val) ?>">
                            <?php else: ?>
                                <textarea name="<?= e($name) ?>" rows="<?= $type === 'short' ? 2 : ($type === 'long' ? 6 : 4) ?>"><?= e($val) ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </<?= $compact ? 'div' : 'section' ?>>
    <?php
    return (string)ob_get_clean();
}

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
    <link rel="icon" href="<?= e(base_url('assets/img/favicon.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(base_url('assets/img/apple-touch-icon.png')) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cairo:wght@600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
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
