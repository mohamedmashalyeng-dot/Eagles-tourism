<?php
/** Reusable public-site HTML fragments. */

function img_tag(?string $path, string $alt, string $class = '', string $extra = ''): string
{
    $fallback = e(base_url('assets/img/placeholder.svg'));
    return '<img src="' . e(img_src($path)) . '" alt="' . e($alt) . '"'
        . ($class !== '' ? ' class="' . e($class) . '"' : '')
        . ' loading="lazy" decoding="async" onerror="this.onerror=null;this.src=\'' . $fallback . '\'" ' . $extra . '>';
}

function trip_card(array $t): string
{
    $url = e(base_url('trip.php?id=' . (int)$t['id']));
    ob_start(); ?>
    <article class="trip-card">
        <a href="<?= $url ?>" class="trip-card__media">
            <?= img_tag($t['cover_image'], $t['title']) ?>
            <span class="trip-card__cat"><?= e(category_label($t['category'])) ?></span>
            <?php if ((float)$t['old_price'] > (float)$t['price_adult']): ?>
                <span class="trip-card__badge">خصم <?= (int)round(100 - ((float)$t['price_adult'] / (float)$t['old_price']) * 100) ?>%</span>
            <?php endif; ?>
        </a>
        <div class="trip-card__body">
            <h3 class="trip-card__title"><a href="<?= $url ?>"><?= e($t['title']) ?></a></h3>
            <ul class="trip-card__meta">
                <?php if ($t['duration']): ?><li><?= icon('clock') ?><?= e($t['duration']) ?></li><?php endif; ?>
                <?php if ($t['location']): ?><li><?= icon('pin') ?><?= e($t['location']) ?></li><?php endif; ?>
            </ul>
            <p class="trip-card__text"><?= e(excerpt($t['short_description'], 110)) ?></p>
            <div class="trip-card__foot">
                <div class="trip-card__price">
                    <small>يبدأ من</small>
                    <?php if ((float)$t['old_price'] > (float)$t['price_adult']): ?><del><?= e(price($t['old_price'])) ?></del><?php endif; ?>
                    <strong><?= e(price($t['price_adult'])) ?></strong>
                </div>
                <a href="<?= $url ?>" class="btn btn-sm btn-outline">التفاصيل <?= icon('arrow') ?></a>
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
            <nav class="crumbs" aria-label="مسار الصفحة"><a href="<?= e(base_url()) ?>">الرئيسية</a><span>/</span><span><?= e($title) ?></span></nav>
            <h1><?= e($title) ?></h1>
            <?php if ($subtitle !== ''): ?><p><?= e($subtitle) ?></p><?php endif; ?>
        </div>
    </section>
    <?php
    return (string)ob_get_clean();
}
