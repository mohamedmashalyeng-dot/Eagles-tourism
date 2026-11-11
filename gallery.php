<?php
require __DIR__ . '/includes/bootstrap.php';

$places = db()->query('SELECT * FROM places WHERE is_active = 1 ORDER BY sort_order ASC, id DESC')->fetchAll();

$pageTitle = 'معالم الغردقة';
$pageDesc  = 'صور لأجمل الأماكن والمعالم السياحية في الغردقة والبحر الأحمر: الجزر، الشعاب المرجانية، الصحراء، والمارينا.';
$activeNav = 'gallery';
require __DIR__ . '/includes/header.php';
echo page_banner('معالم الغردقة', 'جولة مصورة في أجمل أماكن الغردقة والبحر الأحمر', $places[0]['image'] ?? null);
?>
<section class="section">
    <div class="container">
        <?php if ($places): ?>
            <div class="gallery-grid" data-lightbox-group>
                <?php foreach ($places as $p): ?>
                    <figure class="gallery-item">
                        <a href="<?= e(img_src($p['image'])) ?>" data-lightbox data-caption="<?= e($p['name'] . ' — ' . $p['description']) ?>">
                            <?= img_tag($p['image'], $p['name']) ?>
                            <span class="gallery-item__zoom"><?= icon('search') ?></span>
                        </a>
                        <figcaption>
                            <h3><?= e($p['name']) ?></h3>
                            <?php if ($p['description']): ?><p><?= e($p['description']) ?></p><?php endif; ?>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">سيتم إضافة صور المعالم قريباً.</p>
        <?php endif; ?>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-band__inner">
        <div><h2>عايز تزور الأماكن دي؟</h2><p>اختار الرحلة المناسبة لك وإحنا نتولى الباقي.</p></div>
        <a href="<?= e(base_url('trips.php')) ?>" class="btn btn-gold btn-lg">تصفح الرحلات <?= icon('arrow') ?></a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
