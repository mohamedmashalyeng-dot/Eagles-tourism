<?php
require __DIR__ . '/includes/bootstrap.php';

$cat  = (string)($_GET['cat'] ?? '');
$q    = trim((string)($_GET['q'] ?? ''));
$sort = (string)($_GET['sort'] ?? '');

$where = ['is_active = 1'];
$params = [];
if (isset(TRIP_CATEGORIES[$cat])) {
    $where[] = 'category = ?';
    $params[] = $cat;
} else {
    $cat = '';
}
if ($q !== '') {
    // search the Arabic text and every translation, so a visitor can type in any language
    $where[] = '(title LIKE ? OR short_description LIKE ? OR location LIKE ?
                 OR id IN (SELECT trip_id FROM trip_translations WHERE title LIKE ? OR short_description LIKE ? OR location LIKE ?))';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
$order = match ($sort) {
    'price_asc'  => 'price_adult ASC',
    'price_desc' => 'price_adult DESC',
    'new'        => 'id DESC',
    default      => 'is_featured DESC, sort_order ASC, id DESC',
};
$st = db()->prepare('SELECT * FROM trips WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order);
$st->execute($params);
$trips = localize_trips($st->fetchAll());

$pageTitle = $cat ? category_label($cat) : t('الرحلات');
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
echo page_banner($pageTitle, t('اختر رحلتك من بين أفضل الرحلات البحرية والصحراوية والتاريخية في الغردقة'));
?>
<section class="section">
    <div class="container">
        <form class="filters" method="get">
            <?php if (lang() !== DEFAULT_LANG): ?><input type="hidden" name="lang" value="<?= e(lang()) ?>"><?php endif; ?>
            <div class="chips" role="tablist" aria-label="<?= e(t('أنواع الرحلات')) ?>">
                <a class="chip <?= $cat === '' ? 'is-active' : '' ?>" href="<?= e(url('trips.php' . ($q !== '' ? '?q=' . rawurlencode($q) : ''))) ?>"><?= e(t('الكل')) ?></a>
                <?php foreach (array_keys(TRIP_CATEGORIES) as $k): ?>
                    <a class="chip <?= $cat === $k ? 'is-active' : '' ?>" href="<?= e(url('trips.php?cat=' . $k . ($q !== '' ? '&q=' . rawurlencode($q) : ''))) ?>"><?= e(category_label($k)) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="filters__right">
                <?php if ($cat): ?><input type="hidden" name="cat" value="<?= e($cat) ?>"><?php endif; ?>
                <label class="search-input"><?= icon('search') ?><input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('ابحث عن رحلة')) ?>"></label>
                <select name="sort" onchange="this.form.submit()" aria-label="<?= e(t('ترتيب')) ?>">
                    <option value=""><?= e(t('الأكثر طلباً')) ?></option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>><?= e(t('السعر: الأقل أولاً')) ?></option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>><?= e(t('السعر: الأعلى أولاً')) ?></option>
                    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>><?= e(t('الأحدث')) ?></option>
                </select>
            </div>
        </form>

        <p class="results-count"><?= e(category_count(count($trips), $cat)) ?><?= $q !== '' ? ' ' . e(t('لنتيجة البحث "{q}"', ['q' => $q])) : '' ?></p>

        <?php if ($trips): ?>
            <div class="trip-grid">
                <?php foreach ($trips as $t) echo trip_card($t); ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <?= icon('search', 'icon icon-xl') ?>
                <h3><?= e(t('لا توجد رحلات مطابقة')) ?></h3>
                <p><?= e(t('جرّب بحثاً آخر أو تواصل معنا وسنرتب لك الرحلة التي تريدها.')) ?></p>
                <a href="<?= e(url('trips.php')) ?>" class="btn btn-primary"><?= e(t('عرض كل الرحلات')) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
