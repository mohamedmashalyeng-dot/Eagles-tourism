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
                <?php if (has_public_price($t)): ?>
                <div class="trip-card__price"><small><?= e(t('سعر الفرد')) ?></small><strong><?= e(public_price($t)) ?></strong></div>
                <?php else: ?>
                <a class="trip-card__ask" href="<?= e(whatsapp_link(t('مرحباً، أريد الاستفسار عن سعر: {trip}', ['trip' => $t['title']]))) ?>" target="_blank" rel="noopener"><?= icon('chat') ?><?= e(t('اسأل عن السعر')) ?></a>
                <?php endif; ?>
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

function locations_section(): string
{
    ob_start(); ?>
    <section class="section section--tint" id="locations">
        <div class="container">
            <div class="section-head"><div><span class="kicker"><?= e(t('تواصل معنا')) ?></span><h2><?= e(t('عناويننا في الأحياء')) ?></h2></div></div>
            <div class="locations-grid">
                <?php foreach (business_locations() as $location): ?>
                <article class="location-card">
                    <div class="location-card__body">
                        <h3><?= e($location['name']) ?></h3>
                        <p><?= e(t('الأحياء، الغردقة، البحر الأحمر، مصر')) ?></p>
                        <a class="btn btn-outline btn-sm" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($location['query']) ?>" target="_blank" rel="noopener"><?= icon('pin') ?> <?= e(t('بحث العنوان على الخريطة')) ?></a>
                    </div>
                    <div class="map">
                        <iframe title="<?= e(t('خريطة بحث: {name}', ['name' => $location['name']])) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=<?= rawurlencode($location['query']) ?>&amp;z=14&amp;hl=<?= e(lang()) ?>&amp;output=embed"></iframe>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return (string)ob_get_clean();
}
