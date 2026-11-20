<?php
/** Reusable public-site HTML fragments. */

function img_tag(?string $path, string $alt, string $class = '', string $extra = ''): string
{
    $fallback = e(base_url('assets/img/placeholder.svg'));
    return '<img src="' . e(img_src($path)) . '" alt="' . e($alt) . '"'
        . ($class !== '' ? ' class="' . e($class) . '"' : '')
        . ' loading="lazy" decoding="async" onerror="this.onerror=null;this.src=\'' . $fallback . '\'" ' . $extra . '>';
}

/** @param array $t a trip row already passed through localize_trips() */
function trip_card(array $t): string
{
    $url = e(url('trip.php?id=' . (int)$t['id']));
    ob_start(); ?>
    <article class="trip-card">
        <a href="<?= $url ?>" class="trip-card__media">
            <?= img_tag($t['cover_image'], $t['title']) ?>
            <span class="trip-card__cat"><?= e(category_label($t['category'])) ?></span>
        </a>
        <div class="trip-card__body">
            <h3 class="trip-card__title"><a href="<?= $url ?>"><?= e($t['title']) ?></a></h3>
            <ul class="trip-card__meta">
                <?php if ($t['duration']): ?><li><?= icon('clock') ?><?= e($t['duration']) ?></li><?php endif; ?>
                <?php if ($t['location']): ?><li><?= icon('pin') ?><?= e($t['location']) ?></li><?php endif; ?>
            </ul>
            <p class="trip-card__text"><?= e(excerpt($t['short_description'], 110)) ?></p>
            <div class="trip-card__foot">
                <a class="trip-card__ask" href="<?= e(whatsapp_link(t('مرحباً، أريد الاستفسار عن سعر: {trip}', ['trip' => $t['title']]))) ?>" target="_blank" rel="noopener"><?= icon('chat') ?><?= e(t('اسأل عن السعر')) ?></a>
                <a href="<?= $url ?>" class="btn btn-sm btn-outline"><?= e(t('التفاصيل')) ?> <?= icon('arrow') ?></a>
            </div>
        </div>
    </article>
    <?php
    return (string)ob_get_clean();
}

function page_banner(string $title, string $subtitle = '', ?string $image = null): string
{
    $img = $image ?: setting('hero_image');
    ob_start(); ?>
    <section class="page-banner" style="--bg:url('<?= e(img_src($img)) ?>')">
        <div class="container">
            <nav class="crumbs" aria-label="<?= e(t('مسار الصفحة')) ?>"><a href="<?= e(url()) ?>"><?= e(t('الرئيسية')) ?></a><span>/</span><span><?= e($title) ?></span></nav>
            <h1><?= e($title) ?></h1>
            <?php if ($subtitle !== ''): ?><p><?= e($subtitle) ?></p><?php endif; ?>
        </div>
    </section>
    <?php
    return (string)ob_get_clean();
}
