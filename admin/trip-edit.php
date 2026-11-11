<?php
require __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$trip = [
    'id' => 0, 'title' => '', 'category' => 'sea', 'short_description' => '', 'description' => '', 'duration' => '', 'location' => '',
    'pickup_time' => '', 'group_size' => '', 'price_adult' => '', 'price_child' => '', 'old_price' => '', 'includes' => '', 'excludes' => '',
    'itinerary' => '', 'cover_image' => '', 'is_featured' => 0, 'is_active' => 1, 'sort_order' => 0,
];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM trips WHERE id = ?');
    $st->execute([$id]);
    $found = $st->fetch();
    if (!$found) {
        flash('error', 'الرحلة غير موجودة.');
        redirect('admin/trips.php');
    }
    $trip = $found;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $errors[] = 'حجم الصور المرفوعة أكبر من الحد المسموح على الاستضافة (' . ini_get('post_max_size') . '). ارفع عدداً أقل من الصور في كل مرة.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $in = fn(string $k) => trim((string)($_POST[$k] ?? ''));
    $data = [
        'title' => $in('title'),
        'category' => isset(TRIP_CATEGORIES[$in('category')]) ? $in('category') : 'sea',
        'short_description' => mb_substr($in('short_description'), 0, 500),
        'description' => $in('description'),
        'duration' => mb_substr($in('duration'), 0, 100),
        'location' => mb_substr($in('location'), 0, 150),
        'pickup_time' => mb_substr($in('pickup_time'), 0, 100),
        'group_size' => mb_substr($in('group_size'), 0, 100),
        'price_adult' => (float)str_replace(',', '', $in('price_adult')),
        'price_child' => (float)str_replace(',', '', $in('price_child')),
        'old_price' => (float)str_replace(',', '', $in('old_price')),
        'includes' => $in('includes'),
        'excludes' => $in('excludes'),
        'itinerary' => $in('itinerary'),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => (int)$in('sort_order'),
        'cover_image' => $trip['cover_image'],
    ];
    if (mb_strlen($data['title']) < 3) $errors[] = 'اكتب اسم الرحلة.';
    if ($data['price_adult'] <= 0) $errors[] = 'اكتب سعر الفرد (البالغ).';
    if ($data['short_description'] === '') $errors[] = 'اكتب وصفاً مختصراً للرحلة.';
    $coverUrl = $in('cover_url');
    if ($coverUrl !== '' && !preg_match('#^https?://#i', $coverUrl)) $errors[] = 'رابط صورة الغلاف يجب أن يبدأ بـ http أو https.';

    $newPaths = [];
    $oldCover = $data['cover_image'];
    if (!$errors) {
        try {
            $up = upload_image($_FILES['cover'] ?? null, 'trips');
            if ($up) {
                $data['cover_image'] = $up;
                $newPaths[] = $up;
            } elseif ($coverUrl !== '') {
                $data['cover_image'] = $coverUrl;
            }
            $galleryNew = [];
            foreach (normalize_files($_FILES['gallery'] ?? null) as $f) {
                $p = upload_image($f, 'trips');
                if ($p) { $galleryNew[] = $p; $newPaths[] = $p; }
            }
            foreach (lines($_POST['gallery_urls'] ?? '') as $url) {
                if (preg_match('#^https?://#i', $url)) $galleryNew[] = $url;
            }
        } catch (RuntimeException $ex) {
            foreach ($newPaths as $p) delete_upload($p);
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        if ($data['cover_image'] === '' && $galleryNew) {
            $data['cover_image'] = array_shift($galleryNew);
        }
        $cols = array_keys($data);
        if ($id) {
            $set = implode(', ', array_map(fn($c) => "$c = ?", $cols));
            $pdo->prepare("UPDATE trips SET $set, updated_at = ? WHERE id = ?")->execute([...array_values($data), now(), $id]);
        } else {
            $ph = implode(', ', array_fill(0, count($cols), '?'));
            $pdo->prepare('INSERT INTO trips (' . implode(', ', $cols) . ", created_at, updated_at) VALUES ($ph, ?, ?)")
                ->execute([...array_values($data), now(), now()]);
            $id = (int)$pdo->lastInsertId();
        }

        if ($oldCover !== $data['cover_image']) {
            delete_upload($oldCover);
        }

        // remove selected gallery images
        $remove = array_map('intval', (array)($_POST['remove_images'] ?? []));
        if ($remove) {
            $sel = $pdo->prepare('SELECT id, path FROM trip_images WHERE trip_id = ? AND id = ?');
            $del = $pdo->prepare('DELETE FROM trip_images WHERE id = ?');
            foreach ($remove as $rid) {
                $sel->execute([$id, $rid]);
                if ($row = $sel->fetch()) {
                    $del->execute([$row['id']]);
                    delete_upload($row['path']);
                }
            }
        }
        // add new gallery images
        if ($galleryNew) {
            $max = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM trip_images WHERE trip_id = ?');
            $max->execute([$id]);
            $order = (int)$max->fetchColumn();
            $ins = $pdo->prepare('INSERT INTO trip_images (trip_id, path, sort_order) VALUES (?, ?, ?)');
            foreach ($galleryNew as $p) $ins->execute([$id, $p, ++$order]);
        }
        flash('success', 'تم حفظ الرحلة بنجاح.');
        redirect('admin/trip-edit.php?id=' . $id);
    }
    $trip = array_merge($trip, $data, ['cover_url' => $coverUrl]);
}

$images = [];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM trip_images WHERE trip_id = ? ORDER BY sort_order, id');
    $st->execute([$id]);
    $images = $st->fetchAll();
}
$coverIsUrl = preg_match('#^https?://#i', (string)$trip['cover_image']);
$num = fn($v) => (float)$v > 0 ? rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') : '';

admin_header($id ? 'تعديل رحلة' : 'إضافة رحلة جديدة', 'trips');
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="form edit-layout">
    <?= csrf_field() ?>
    <div class="edit-main">
        <section class="panel">
            <h2 class="panel__title">بيانات الرحلة</h2>
            <div class="field"><label>اسم الرحلة *</label><input name="title" required value="<?= e($trip['title']) ?>" placeholder="مثال: رحلة جزيرة أورانج باي"></div>
            <div class="grid-2">
                <div class="field"><label>نوع الرحلة</label>
                    <select name="category"><?php foreach (TRIP_CATEGORIES as $k => $l): ?><option value="<?= e($k) ?>" <?= $trip['category'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
                </div>
                <div class="field"><label>المكان</label><input name="location" value="<?= e($trip['location']) ?>" placeholder="مثال: جزيرة الجفتون"></div>
                <div class="field"><label>مدة الرحلة</label><input name="duration" value="<?= e($trip['duration']) ?>" placeholder="مثال: 8 ساعات"></div>
                <div class="field"><label>موعد الاستلام</label><input name="pickup_time" value="<?= e($trip['pickup_time']) ?>" placeholder="مثال: 8:00 صباحاً من الفندق"></div>
                <div class="field"><label>حجم المجموعة</label><input name="group_size" value="<?= e($trip['group_size']) ?>" placeholder="مثال: حتى 30 فرد"></div>
            </div>
            <div class="field"><label>وصف مختصر * <small>(يظهر في كارت الرحلة)</small></label><textarea name="short_description" rows="2" maxlength="500" required><?= e($trip['short_description']) ?></textarea></div>
            <div class="field"><label>تفاصيل الرحلة</label><textarea name="description" rows="7"><?= e($trip['description']) ?></textarea></div>
        </section>

        <section class="panel">
            <h2 class="panel__title">ماذا تشمل الرحلة؟ <small>(اكتب كل بند في سطر)</small></h2>
            <div class="grid-2">
                <div class="field"><label>السعر يشمل</label><textarea name="includes" rows="6" placeholder="انتقالات من وإلى الفندق&#10;وجبة غداء&#10;معدات السنوركل"><?= e($trip['includes']) ?></textarea></div>
                <div class="field"><label>السعر لا يشمل</label><textarea name="excludes" rows="6" placeholder="التصوير تحت الماء&#10;المشروبات الإضافية"><?= e($trip['excludes']) ?></textarea></div>
            </div>
            <div class="field"><label>برنامج الرحلة <small>(كل خطوة في سطر — يمكن البدء بالوقت مثل: 09:00 - الإبحار)</small></label>
                <textarea name="itinerary" rows="7" placeholder="08:00 - الاستلام من الفندق&#10;09:00 - الإبحار من المارينا"><?= e($trip['itinerary']) ?></textarea></div>
        </section>

        <section class="panel">
            <h2 class="panel__title">صور الرحلة</h2>
            <?php if ($images): ?>
                <p class="hint">علّم على الصور التي تريد حذفها ثم اضغط حفظ.</p>
                <div class="img-manage">
                    <?php foreach ($images as $img): ?>
                        <label class="img-manage__item">
                            <?= img_tag($img['path'], 'صورة') ?>
                            <span><input type="checkbox" name="remove_images[]" value="<?= (int)$img['id'] ?>"> حذف</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="field">
                <label>إضافة صور للمعرض <small>(يمكن اختيار أكثر من صورة — JPG / PNG / WEBP)</small></label>
                <input type="file" name="gallery[]" accept="image/*" multiple data-preview="galleryPreview">
                <div class="preview" id="galleryPreview"></div>
            </div>
            <details class="field">
                <summary>أو أضف صوراً عن طريق روابط</summary>
                <textarea name="gallery_urls" rows="3" dir="ltr" placeholder="https://..."></textarea>
            </details>
        </section>
    </div>

    <aside class="edit-side">
        <section class="panel">
            <h2 class="panel__title">النشر</h2>
            <label class="check"><input type="checkbox" name="is_active" <?= $trip['is_active'] ? 'checked' : '' ?>> منشورة على الموقع</label>
            <label class="check"><input type="checkbox" name="is_featured" <?= $trip['is_featured'] ? 'checked' : '' ?>> رحلة مميزة (تظهر في الرئيسية)</label>
            <div class="field"><label>ترتيب العرض <small>(الأصغر يظهر أولاً)</small></label><input type="number" name="sort_order" value="<?= (int)$trip['sort_order'] ?>"></div>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> حفظ الرحلة</button>
            <?php if ($id): ?><a class="btn btn-light btn-block" href="<?= e(base_url('trip.php?id=' . $id)) ?>" target="_blank"><?= icon('eye') ?> معاينة</a><?php endif; ?>
        </section>

        <section class="panel">
            <h2 class="panel__title">التكلفة (<?= e(setting('currency', 'ج.م')) ?>)</h2>
            <div class="field"><label>سعر الفرد (بالغ) *</label><input name="price_adult" type="number" step="0.01" min="0" required value="<?= e($num($trip['price_adult'])) ?>"></div>
            <div class="field"><label>سعر الطفل</label><input name="price_child" type="number" step="0.01" min="0" value="<?= e($num($trip['price_child'])) ?>"></div>
            <div class="field"><label>السعر قبل الخصم <small>(اختياري)</small></label><input name="old_price" type="number" step="0.01" min="0" value="<?= e($num($trip['old_price'])) ?>"></div>
        </section>

        <section class="panel">
            <h2 class="panel__title">صورة الغلاف</h2>
            <div class="cover-preview" id="coverPreview"><?= img_tag($trip['cover_image'], 'صورة الغلاف') ?></div>
            <div class="field"><label>رفع صورة</label><input type="file" name="cover" accept="image/*" data-preview="coverPreview"></div>
            <div class="field"><label>أو رابط صورة</label><input name="cover_url" dir="ltr" value="<?= e($trip['cover_url'] ?? ($coverIsUrl ? $trip['cover_image'] : '')) ?>" placeholder="https://..."></div>
        </section>
    </aside>
</form>
<?php admin_footer(); ?>
