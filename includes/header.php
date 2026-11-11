<?php
/** @var string|null $pageTitle  @var string|null $pageDesc  @var string|null $activeNav */
$siteName  = setting('site_name', 'Eagles Tourism');
$fullTitle = !empty($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName . ' | ' . setting('site_tagline');
$metaDesc  = !empty($pageDesc) ? $pageDesc : setting('meta_description');
$activeNav = $activeNav ?? '';
$nav = [
    'home'    => ['الرئيسية', ''],
    'trips'   => ['الرحلات', 'trips.php'],
    'gallery' => ['معالم الغردقة', 'gallery.php'],
    'about'   => ['من نحن', 'index.php#about'],
    'contact' => ['تواصل معنا', 'contact.php'],
];
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e(img_src($ogImage)) ?>"><?php endif; ?>
    <meta name="theme-color" content="#0b2545">
    <link rel="icon" href="<?= e(base_url('assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main">تخطي إلى المحتوى</a>

<header class="site-header" id="siteHeader">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__info">
                <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(setting('phone')) ?></span></a>
                <a href="mailto:<?= e(setting('email')) ?>" class="hide-sm"><?= icon('mail') ?><?= e(setting('email')) ?></a>
            </div>
            <div class="topbar__info">
                <span class="hide-sm"><?= icon('clock') ?><?= e(setting('working_hours')) ?></span>
                <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener" aria-label="فيسبوك"><?= icon('facebook') ?></a><?php endif; ?>
                <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" aria-label="إنستجرام"><?= icon('instagram') ?></a><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="navbar">
        <div class="container navbar__inner">
            <a href="<?= e(base_url()) ?>" class="brand" aria-label="<?= e($siteName) ?>">
                <?= brand_mark() ?>
                <span class="brand__text"><strong>EAGLES</strong><small>TOURISM</small></span>
            </a>
            <nav class="nav" id="mainNav" aria-label="القائمة الرئيسية">
                <?php foreach ($nav as $key => [$label, $href]): ?>
                    <a href="<?= e(base_url($href)) ?>" class="<?= $activeNav === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
                <a href="<?= e(whatsapp_link('مرحباً، أريد الاستفسار عن رحلات إيجلز في الغردقة')) ?>" class="btn btn-gold nav__cta" target="_blank" rel="noopener"><?= icon('chat') ?>احجز الآن</a>
            </nav>
            <button class="nav-toggle" id="navToggle" aria-label="فتح القائمة" aria-expanded="false" aria-controls="mainNav"><?= icon('menu') ?></button>
        </div>
    </div>
</header>

<main id="main">
