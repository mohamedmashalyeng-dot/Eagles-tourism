<?php
require __DIR__ . '/includes/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM trips WHERE id = ? AND is_active = 1');
$st->execute([$id]);
$tripAr = $st->fetch();

if (!$tripAr) {
    http_response_code(404);
    $pageTitle = t('الرحلة غير موجودة');
    require __DIR__ . '/includes/header.php';
    echo page_banner($pageTitle);
    echo '<section class="section"><div class="container empty"><h3>' . e(t('عذراً، هذه الرحلة غير متاحة حالياً')) . '</h3><a class="btn btn-primary" href="' . e(url('trips.php')) . '">' . e(t('تصفح كل الرحلات')) . '</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$trip = localize_trip($tripAr);
/** Trip name for the team: the visitor's language plus the Arabic name when they differ. */
$tripForStaff = $trip['title'] !== $tripAr['title'] ? $trip['title'] . ' (' . $tripAr['title'] . ')' : $trip['title'];

/* ---------- booking request ---------- */
start_session();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $f = [
        'name'     => trim((string)($_POST['name'] ?? '')),
        'phone'    => trim((string)($_POST['phone'] ?? '')),
        'email'    => trim((string)($_POST['email'] ?? '')),
        'date'     => trim((string)($_POST['travel_date'] ?? '')),
        'adults'   => max(1, min(100, (int)($_POST['adults'] ?? 1))),
        'children' => max(0, min(100, (int)($_POST['children'] ?? 0))),
        'hotel'    => trim((string)($_POST['hotel'] ?? '')),
        'notes'    => trim((string)($_POST['notes'] ?? '')),
    ];
    if (!empty($_POST['website'])) { // honeypot
        redirect(url('trip.php?id=' . $id));
    }
    if (mb_strlen($f['name']) < 2) $errors[] = t('برجاء كتابة الاسم.');
    if (!preg_match('/^[0-9+\s()-]{7,20}$/', $f['phone'])) $errors[] = t('برجاء كتابة رقم هاتف صحيح.');
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = t('البريد الإلكتروني غير صحيح.');
    $d = DateTime::createFromFormat('Y-m-d', $f['date']);
    if (!$d || $d->format('Y-m-d') !== $f['date'] || $f['date'] < date('Y-m-d')) $errors[] = t('برجاء اختيار تاريخ رحلة صحيح.');

    if (!$errors) {
        $total = $f['adults'] * (float)$trip['price_adult'] + $f['children'] * (float)$trip['price_child'];
        // the dashboard is Arabic: keep the Arabic trip name and note the visitor's language
        $notes = $f['notes'];
        if (lang() !== DEFAULT_LANG) {
            $notes = '[لغة العميل: ' . LANGS[lang()]['name'] . ']' . ($notes !== '' ? "\n" . $notes : '');
        }
        db()->prepare('INSERT INTO bookings (trip_id, trip_title, name, phone, email, travel_date, adults, children, hotel, notes, total, status, created_at)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$trip['id'], $tripAr['title'], mb_substr($f['name'], 0, 120), $f['phone'], mb_substr($f['email'], 0, 150), $f['date'],
                $f['adults'], $f['children'], mb_substr($f['hotel'], 0, 150), mb_substr($notes, 0, 2000), $total, 'new', now()]);
        $_SESSION['last_booking'] = [
            'trip' => $tripForStaff, 'name' => $f['name'], 'date' => $f['date'],
            'adults' => $f['adults'], 'children' => $f['children'], 'total' => $total, 'hotel' => $f['hotel'],
        ];
        unset($_SESSION['old']);
        redirect(url('trip.php?id=' . $id . '&booked=1#book'));
    }
    $_SESSION['old'] = $_POST;
}
$booked = isset($_GET['booked']) ? ($_SESSION['last_booking'] ?? null) : null;

/* ---------- data ---------- */
$imgs = db()->prepare('SELECT path FROM trip_images WHERE trip_id = ? ORDER BY sort_order, id');
$imgs->execute([$id]);
$gallery = array_values(array_filter(array_merge([$trip['cover_image']], $imgs->fetchAll(PDO::FETCH_COLUMN))));

$rel = db()->prepare('SELECT * FROM trips WHERE is_active = 1 AND id <> ? ORDER BY (category = ?) DESC, is_featured DESC, sort_order LIMIT 3');
$rel->execute([$id, $trip['category']]);
$related = localize_trips($rel->fetchAll());

$isSpa     = is_spa_category($trip['category']);
$includes  = lines($trip['includes']);
$excludes  = lines($trip['excludes']);
$itinerary = lines($trip['itinerary']);

$pageTitle = $trip['title'];
$pageDesc  = excerpt($trip['short_description'], 155);
$ogImage   = $trip['cover_image'];
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
echo page_banner($trip['title'], category_label($trip['category']) . ($trip['location'] ? ' · ' . $trip['location'] : ''), $trip['cover_image']);

$waMsg = t('مرحباً، أريد حجز رحلة: {trip}', ['trip' => $tripForStaff]);
?>
<section class="section trip-page">
    <div class="container trip-layout">
        <div class="trip-main">
            <div class="trip-gallery" data-lightbox-group>
                <a class="trip-gallery__main" href="<?= e(img_src($gallery[0] ?? '')) ?>" data-lightbox data-caption="<?= e($trip['title']) ?>">
                    <?= img_tag($gallery[0] ?? '', $trip['title'], '', 'id="tripMainImg" fetchpriority="high"') ?>
                    <?php if (count($gallery) > 1): ?><span class="trip-gallery__count"><?= icon('image') ?> <?= e(t('{n} صور', ['n' => count($gallery)])) ?></span><?php endif; ?>
                </a>
                <?php if (count($gallery) > 1): ?>
                    <div class="trip-gallery__thumbs">
                        <?php foreach ($gallery as $i => $g): if ($i === 0) continue; ?>
                            <a href="<?= e(img_src($g)) ?>" data-lightbox data-caption="<?= e($trip['title']) ?>"><?= img_tag($g, $trip['title'] . ' - ' . t('صورة {n}', ['n' => $i + 1])) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <ul class="trip-facts">
                <?php if ($trip['duration']): ?><li><?= icon('clock') ?><div><small><?= e(t('المدة')) ?></small><strong><?= e($trip['duration']) ?></strong></div></li><?php endif; ?>
                <?php if ($trip['location']): ?><li><?= icon('pin') ?><div><small><?= e(t('المكان')) ?></small><strong><?= e($trip['location']) ?></strong></div></li><?php endif; ?>
                <?php if ($trip['pickup_time']): ?><li><?= icon('calendar') ?><div><small><?= e(t('موعد الاستلام')) ?></small><strong><?= e($trip['pickup_time']) ?></strong></div></li><?php endif; ?>
                <?php if ($trip['group_size']): ?><li><?= icon('users') ?><div><small><?= e(t('المجموعة')) ?></small><strong><?= e($trip['group_size']) ?></strong></div></li><?php endif; ?>
            </ul>

            <div class="trip-block">
                <h2><?= e($isSpa ? t('عن الجلسة') : t('عن الرحلة')) ?></h2>
                <div class="prose"><?= nl2br(e($trip['description'] ?: $trip['short_description'])) ?></div>
            </div>

            <?php if ($includes || $excludes): ?>
            <div class="trip-block incl-grid">
                <?php if ($includes): ?>
                <div>
                    <h3><?= e(t('السعر يشمل')) ?></h3>
                    <ul class="ticks"><?php foreach ($includes as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
                <?php if ($excludes): ?>
                <div>
                    <h3><?= e(t('السعر لا يشمل')) ?></h3>
                    <ul class="ticks ticks--no"><?php foreach ($excludes as $l): ?><li><?= icon('x') ?><?= e($l) ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($itinerary): ?>
            <div class="trip-block">
                <h2><?= e($isSpa ? t('تفاصيل الجلسة') : t('برنامج الرحلة')) ?></h2>
                <ol class="timeline">
                    <?php foreach ($itinerary as $step):
                        $time = ''; $text = $step;
                        if (preg_match('/^([0-9]{1,2}[:.][0-9]{2})\s*[-–—]\s*(.+)$/u', $step, $m)) { $time = $m[1]; $text = $m[2]; } ?>
                        <li><?php if ($time): ?><time><?= e($time) ?></time><?php endif; ?><span><?= e($text) ?></span></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php endif; ?>
        </div>

        <aside class="trip-aside" id="book">
            <div class="book-card">
                <div class="book-card__price">
                    <small><?= e($isSpa ? t('سعر الجلسة للفرد') : t('سعر الفرد')) ?></small>
                    <div>
                        <?php if ((float)$trip['old_price'] > (float)$trip['price_adult']): ?><del><?= e(price($trip['old_price'])) ?></del><?php endif; ?>
                        <strong><?= e(price($trip['price_adult'])) ?></strong>
                    </div>
                    <?php if ((float)$trip['price_child'] > 0): ?><span><?= e(t('الطفل: {price}', ['price' => price($trip['price_child'])])) ?></span><?php endif; ?>
                </div>

                <?php if ($booked): ?>
                    <div class="book-success">
                        <span class="book-success__icon"><?= icon('check') ?></span>
                        <h3><?= e(t('تم استلام طلب الحجز')) ?></h3>
                        <p><?= e(t('شكراً {name}! سنتواصل معك قريباً لتأكيد الحجز. لتأكيد أسرع أرسل الطلب على واتساب:', ['name' => $booked['name']])) ?></p>
                        <?php $msg = t('مرحباً، قمت بطلب حجز من الموقع:') . "\n"
                            . t('الرحلة: {v}', ['v' => $booked['trip']]) . "\n"
                            . t('الاسم: {v}', ['v' => $booked['name']]) . "\n"
                            . t('التاريخ: {v}', ['v' => $booked['date']]) . "\n"
                            . t('البالغين: {a} - الأطفال: {c}', ['a' => $booked['adults'], 'c' => $booked['children']]) . "\n"
                            . t('الفندق: {v}', ['v' => $booked['hotel']]) . "\n"
                            . t('الإجمالي: {v}', ['v' => price($booked['total'])]); ?>
                        <a class="btn btn-wa btn-block" href="<?= e(whatsapp_link($msg)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> <?= e(t('أرسل على واتساب')) ?></a>
                    </div>
                <?php else: ?>
                    <h3 class="book-card__title"><?= e($isSpa ? t('احجز جلستك الآن') : t('احجز رحلتك الآن')) ?></h3>
                    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
                    <form method="post" action="<?= e(url('trip.php?id=' . $id)) ?>#book" class="form book-form" id="bookForm"
                          data-adult="<?= e((float)$trip['price_adult']) ?>" data-child="<?= e((float)$trip['price_child']) ?>" data-currency="<?= e(setting('currency', 'ج.م')) ?>">
                        <?= csrf_field() ?>
                        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <div class="field"><label for="bName"><?= e(t('الاسم بالكامل')) ?></label><input id="bName" name="name" required value="<?= e(old('name')) ?>"></div>
                        <div class="field"><label for="bPhone"><?= e(t('رقم الهاتف / واتساب')) ?></label><input id="bPhone" name="phone" type="tel" dir="ltr" required value="<?= e(old('phone')) ?>" placeholder="+20 1xx xxx xxxx"></div>
                        <div class="field"><label for="bDate"><?= e(t('التاريخ')) ?></label><input id="bDate" name="travel_date" type="date" required min="<?= date('Y-m-d') ?>" value="<?= e(old('travel_date')) ?>"></div>
                        <div class="grid-2">
                            <div class="field"><label for="bAdults"><?= e(t('البالغين')) ?></label><input id="bAdults" name="adults" type="number" min="1" max="100" value="<?= e(old('adults', '2')) ?>"></div>
                            <div class="field"><label for="bChildren"><?= e(t('الأطفال')) ?></label><input id="bChildren" name="children" type="number" min="0" max="100" value="<?= e(old('children', '0')) ?>"></div>
                        </div>
                        <div class="field"><label for="bHotel"><?= e(t('اسم الفندق (اختياري)')) ?></label><input id="bHotel" name="hotel" value="<?= e(old('hotel')) ?>"></div>
                        <div class="field"><label for="bEmail"><?= e(t('البريد الإلكتروني (اختياري)')) ?></label><input id="bEmail" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>"></div>
                        <div class="field"><label for="bNotes"><?= e(t('ملاحظات (اختياري)')) ?></label><textarea id="bNotes" name="notes" rows="2"><?= e(old('notes')) ?></textarea></div>
                        <div class="book-total"><span><?= e(t('الإجمالي التقديري')) ?></span><strong id="bookTotal"><?= e(price((float)$trip['price_adult'] * 2)) ?></strong></div>
                        <button class="btn btn-primary btn-block btn-lg" type="submit"><?= e(t('تأكيد طلب الحجز')) ?></button>
                    </form>
                    <div class="or"><span><?= e(t('أو')) ?></span></div>
                    <a class="btn btn-wa btn-block" href="<?= e(whatsapp_link($waMsg)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> <?= e(t('احجز عبر واتساب')) ?></a>
                    <?php unset($_SESSION['old']); ?>
                <?php endif; ?>
                <?php if ($policy = lines(setting('booking_policy'))): ?>
                <ul class="book-card__assure">
                    <?php foreach ($policy as $pl): ?><li><?= icon('shield') ?> <?= e($pl) ?></li><?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>

<?php if ($related): ?>
<section class="section section--tint">
    <div class="container">
        <div class="section-head"><div><span class="kicker"><?= e(t('قد تعجبك أيضاً')) ?></span><h2><?= e(t('رحلات مشابهة')) ?></h2></div></div>
        <div class="trip-grid"><?php foreach ($related as $t) echo trip_card($t); ?></div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
