<?php
/** @var string|null $pageTitle  @var string|null $pageDesc  @var string|null $activeNav */
$siteName  = setting('site_name', 'Eagles Tourism');
$fullTitle = !empty($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName . ' | ' . setting('site_tagline');
$metaDesc  = !empty($pageDesc) ? $pageDesc : setting('meta_description');
$activeNav = $activeNav ?? '';
$curLang   = lang();
$nav = [
    'home'    => [t('الرئيسية'), ''],
    'trips'   => [t('الرحلات'), 'trips.php'],
    'massage' => [t('مساج'), 'trips.php?cat=massage'],
    'beauty'  => [t('بيوتي'), 'trips.php?cat=beauty'],
    'gallery' => [t('معالم الغردقة'), 'gallery.php'],
    'about'   => [t('من نحن'), 'index.php#about'],
    'contact' => [t('تواصل معنا'), 'contact.php'],
];
header('Vary: Accept-Language, Cookie');
header('Content-Language: ' . $curLang);
?><!doctype html>
<html lang="<?= e($curLang) ?>" dir="<?= e(lang_dir()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= e(LANGS[$curLang]['locale']) ?>">
    <?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e(img_src($ogImage)) ?>"><?php endif; ?>
    <?php foreach (array_keys(LANGS) as $code): ?>
    <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(lang_url($code, true)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e(lang_url(DEFAULT_LANG, true)) ?>">
    <meta name="theme-color" content="#0b2545">
    <link rel="icon" href="<?= e(base_url('assets/img/favicon.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(base_url('assets/img/apple-touch-icon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if (lang_dir() === 'rtl'): ?>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cairo:wght@500;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <?php else: ?>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Montserrat:wght@500;600;700;800&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main"><?= e(t('تخطي إلى المحتوى')) ?></a>

<header class="site-header" id="siteHeader">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__info">
                <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(setting('phone')) ?></span></a>
                <?php if (setting('email')): ?><a href="mailto:<?= e(setting('email')) ?>" class="hide-sm"><?= icon('mail') ?><?= e(setting('email')) ?></a><?php endif; ?>
            </div>
            <div class="topbar__info">
                <?php if (setting('working_hours')): ?><span class="hide-sm"><?= icon('clock') ?><?= e(setting('working_hours')) ?></span><?php endif; ?>
                <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener" aria-label="<?= e(t('فيسبوك')) ?>"><?= icon('facebook') ?></a><?php endif; ?>
                <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" aria-label="<?= e(t('إنستجرام')) ?>"><?= icon('instagram') ?></a><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="navbar">
        <div class="container navbar__inner">
            <a href="<?= e(url()) ?>" class="brand" aria-label="<?= e($siteName) ?>">
                <?= brand_mark() ?>
                <span class="brand__text"><strong>EAGLES</strong><small>TOURISM</small></span>
            </a>
            <nav class="nav" id="mainNav" aria-label="<?= e(t('القائمة الرئيسية')) ?>">
                <?php foreach ($nav as $key => [$label, $href]): ?>
                    <a href="<?= e(url($href)) ?>" class="<?= $activeNav === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
                <a href="<?= e(whatsapp_link(t('مرحباً، أريد الاستفسار عن رحلات إيجلز في الغردقة'))) ?>" class="btn btn-gold nav__cta" target="_blank" rel="noopener"><?= icon('chat') ?><?= e(t('احجز الآن')) ?></a>
            </nav>
            <details class="lang-switch">
                <summary aria-label="<?= e(t('اللغة')) ?>"><?= icon('globe') ?><span><?= e(LANGS[$curLang]['short']) ?></span></summary>
                <ul class="lang-switch__menu">
                    <?php foreach (LANGS as $code => $info): ?>
                        <li><a href="<?= e(lang_url($code)) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>" class="<?= $code === $curLang ? 'is-active' : '' ?>"><b><?= e($info['short']) ?></b><?= e($info['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </details>
            <button class="nav-toggle" id="navToggle" aria-label="<?= e(t('فتح القائمة')) ?>" aria-expanded="false" aria-controls="mainNav"><?= icon('menu') ?></button>
        </div>
    </div>
</header>

<main id="main">
