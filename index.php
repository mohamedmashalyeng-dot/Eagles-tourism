<?php
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$featured = $pdo->query('SELECT * FROM trips WHERE is_active = 1 ORDER BY is_featured DESC, sort_order ASC, id DESC LIMIT 6')->fetchAll();
$places   = $pdo->query('SELECT * FROM places WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 9')->fetchAll();
$tripCount = (int)$pdo->query('SELECT COUNT(*) FROM trips WHERE is_active = 1')->fetchColumn();
$catCounts = [];
foreach ($pdo->query('SELECT category, COUNT(*) c FROM trips WHERE is_active = 1 GROUP BY category') as $r) {
    $catCounts[$r['category']] = (int)$r['c'];
}

$activeNav = 'home';
$ogImage = setting('hero_image');
require __DIR__ . '/includes/header.php';

$catIcons = ['sea' => 'anchor', 'safari' => 'sun', 'diving' => 'compass', 'day' => 'award', 'city' => 'pin', 'family' => 'heart'];
?>

<section class="hero" style="--bg:url('<?= e(img_src(setting('hero_image'))) ?>')">
    <div class="container hero__inner">
        <span class="eyebrow"><?= icon('pin') ?> الغردقة · البحر الأحمر</span>
        <h1 class="hero__title"><?= e(setting('hero_title')) ?></h1>
        <p class="hero__text"><?= e(setting('hero_subtitle')) ?></p>
        <div class="hero__actions">
            <a href="<?= e(base_url('trips.php')) ?>" class="btn btn-gold btn-lg">تصفح الرحلات <?= icon('arrow') ?></a>
            <a href="<?= e(whatsapp_link('مرحباً، أريد حجز رحلة في الغردقة')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= icon('chat') ?> تواصل عبر واتساب</a>
        </div>
        <form class="hero-search" action="<?= e(base_url('trips.php')) ?>" method="get" role="search">
            <label class="hero-search__field">
                <?= icon('search') ?>
                <input type="search" name="q" placeholder="ابحث عن رحلة... (مثال: أورانج باي، سفاري، غوص)" aria-label="بحث عن رحلة">
            </label>
            <label class="hero-search__field hero-search__select">
                <?= icon('compass') ?>
                <select name="cat" aria-label="نوع الرحلة">
                    <option value="">كل أنواع الرحلات</option>
                    <?php foreach (TRIP_CATEGORIES as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-primary" type="submit">ابحث</button>
        </form>
    </div>
    <div class="hero__scroll" aria-hidden="true"></div>
</section>

<section class="stats">
    <div class="container stats__grid">
        <div class="stat"><strong><?= max($tripCount, 1) ?>+</strong><span>رحلة مميزة</span></div>
        <div class="stat"><strong><?= e(setting('stat_clients', '5000+')) ?></strong><span>عميل سعيد</span></div>
        <div class="stat"><strong><?= e(setting('stat_rating', '4.9')) ?></strong><span>تقييم العملاء</span></div>
        <div class="stat"><strong>24/7</strong><span>دعم ومتابعة</span></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">رحلاتنا المميزة</span>
                <h2>أشهر الرحلات في الغردقة</h2>
                <p>اخترنا لك أكثر الرحلات طلباً، بأسعار واضحة تشمل الانتقالات من وإلى فندقك.</p>
            </div>
            <a href="<?= e(base_url('trips.php')) ?>" class="btn btn-outline">كل الرحلات <?= icon('arrow') ?></a>
        </div>
        <?php if ($featured): ?>
            <div class="trip-grid">
                <?php foreach ($featured as $t) echo trip_card($t); ?>
            </div>
        <?php else: ?>
            <p class="empty">سيتم إضافة الرحلات قريباً.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <div class="section-head section-head--center">
            <div>
                <span class="kicker">اختر مغامرتك</span>
                <h2>أنواع الرحلات</h2>
            </div>
        </div>
        <div class="cat-grid">
            <?php foreach (TRIP_CATEGORIES as $k => $label): ?>
                <a class="cat-card" href="<?= e(base_url('trips.php?cat=' . $k)) ?>">
                    <span class="cat-card__icon"><?= icon($catIcons[$k] ?? 'compass') ?></span>
                    <strong><?= e($label) ?></strong>
                    <small><?= (int)($catCounts[$k] ?? 0) ?> رحلة</small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($places): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="kicker">معالم الغردقة</span>
                <h2>أماكن تستحق الزيارة</h2>
                <p>جزر ساحرة، شعاب مرجانية، صحراء ذهبية ومعالم مميزة — هذه بعض الأماكن التي ستزورها معنا.</p>
            </div>
            <a href="<?= e(base_url('gallery.php')) ?>" class="btn btn-outline">المعرض الكامل <?= icon('arrow') ?></a>
        </div>
        <div class="places-mosaic" data-lightbox-group>
            <?php foreach ($places as $i => $p): ?>
                <a class="place-tile <?= $i === 0 ? 'place-tile--big' : '' ?>" href="<?= e(img_src($p['image'])) ?>" data-lightbox data-caption="<?= e($p['name'] . ' — ' . $p['description']) ?>">
                    <?= img_tag($p['image'], $p['name']) ?>
                    <span class="place-tile__info"><strong><?= e($p['name']) ?></strong><small><?= e(excerpt($p['description'], 70)) ?></small></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--navy" id="about">
    <div class="container about">
        <div class="about__text">
            <span class="kicker kicker--gold">من نحن</span>
            <h2>لماذا تختار إيجلز للسياحة؟</h2>
            <p><?= nl2br(e(setting('about_text'))) ?></p>
            <ul class="features">
                <li><span><?= icon('shield') ?></span><div><strong>أمان وتأمين كامل</strong><small>يخوت مرخصة ومرشدون ومنقذون محترفون في كل رحلة.</small></div></li>
                <li><span><?= icon('tag') ?></span><div><strong>أسعار واضحة بدون مفاجآت</strong><small>السعر يشمل الانتقالات وكل ما هو مذكور في تفاصيل الرحلة.</small></div></li>
                <li><span><?= icon('users') ?></span><div><strong>فريق محلي خبير</strong><small>نعرف الغردقة جيداً ونرشح لك الأنسب لك ولعائلتك.</small></div></li>
                <li><span><?= icon('chat') ?></span><div><strong>حجز سهل وسريع</strong><small>احجز من الموقع أو عبر واتساب وسنتواصل معك فوراً.</small></div></li>
            </ul>
        </div>
        <div class="about__media">
            <?php $aboutImgs = array_slice(array_column($places, 'image'), 1, 2); ?>
            <?= img_tag($aboutImgs[0] ?? setting('hero_image'), 'رحلات إيجلز في الغردقة', 'about__img1') ?>
            <?= img_tag($aboutImgs[1] ?? setting('hero_image'), 'البحر الأحمر', 'about__img2') ?>
            <div class="about__badge"><strong>+<?= e(setting('stat_years', '10')) ?></strong><small>سنوات خبرة<br>في البحر الأحمر</small></div>
        </div>
    </div>
</section>

<?php
$reviews = [];
foreach (lines(setting('testimonials')) as $line) {
    $parts = array_map('trim', explode('|', $line));
    if (count($parts) >= 3) $reviews[] = $parts;
}
if ($reviews): ?>
<section class="section section--tint">
    <div class="container">
        <div class="section-head section-head--center">
            <div><span class="kicker">آراء عملائنا</span><h2>ماذا قالوا عن رحلاتنا</h2></div>
        </div>
        <div class="reviews">
            <?php foreach (array_slice($reviews, 0, 6) as [$name, $from, $text]): ?>
                <figure class="review">
                    <div class="review__stars"><?= str_repeat(icon('star'), 5) ?></div>
                    <blockquote><?= e($text) ?></blockquote>
                    <figcaption><span class="review__avatar"><?= e(mb_substr($name, 0, 1)) ?></span><div><strong><?= e($name) ?></strong><small><?= e($from) ?></small></div></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-band__inner">
        <div>
            <h2>جاهز لمغامرتك القادمة في الغردقة؟</h2>
            <p>تواصل معنا الآن واحصل على أفضل عرض لرحلتك أو لمجموعتك.</p>
        </div>
        <div class="btn-row">
            <a href="<?= e(whatsapp_link('مرحباً، أريد عرض سعر لرحلة')) ?>" class="btn btn-gold btn-lg" target="_blank" rel="noopener"><?= icon('chat') ?> واتساب</a>
            <a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>" class="btn btn-ghost btn-lg"><?= icon('phone') ?> اتصل بنا</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
