<?php
require __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

$editId = (int)($_GET['edit'] ?? 0);
$place = ['id' => 0, 'name' => '', 'description' => '', 'image' => '', 'is_active' => 1, 'sort_order' => 0];
if ($editId) {
    $st = $pdo->prepare('SELECT * FROM places WHERE id = ?');
    $st->execute([$editId]);
    $place = $st->fetch() ?: $place;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $errors[] = 'حجم الصورة أكبر من الحد المسموح على الاستضافة (' . ini_get('post_max_size') . ').';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? 'save');
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $st = $pdo->prepare('SELECT image FROM places WHERE id = ?');
        $st->execute([$id]);
        $img = $st->fetchColumn();
        $pdo->prepare('DELETE FROM places WHERE id = ?')->execute([$id]);
        delete_upload($img ?: null);
        flash('success', 'تم حذف الصورة.');
        redirect('admin/places.php');
    }
    if ($action === 'toggle' && $id) {
        $pdo->prepare('UPDATE places SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        redirect('admin/places.php');
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $desc = mb_substr(trim((string)($_POST['description'] ?? '')), 0, 600);
    $url  = trim((string)($_POST['image_url'] ?? ''));
    $order = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    $current = '';
    if ($id) {
        $st = $pdo->prepare('SELECT image FROM places WHERE id = ?');
        $st->execute([$id]);
        $current = (string)$st->fetchColumn();
    }
    if (mb_strlen($name) < 2) $errors[] = 'اكتب اسم المكان.';
    if ($url !== '' && !preg_match('#^https?://#i', $url)) $errors[] = 'رابط الصورة غير صحيح.';
    $image = $current;
    if (!$errors) {
        try {
            $up = upload_image($_FILES['image'] ?? null, 'places');
            if ($up) $image = $up;
            elseif ($url !== '') $image = $url;
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
        if (!$errors && $image === '') $errors[] = 'اختر صورة للمكان.';
    }
    if (!$errors) {
        if ($id) {
            $pdo->prepare('UPDATE places SET name = ?, description = ?, image = ?, is_active = ?, sort_order = ? WHERE id = ?')
                ->execute([$name, $desc, $image, $active, $order, $id]);
            if ($current !== $image) delete_upload($current);
        } else {
            $pdo->prepare('INSERT INTO places (name, description, image, is_active, sort_order, created_at) VALUES (?,?,?,?,?,?)')
                ->execute([$name, $desc, $image, $active, $order, now()]);
        }
        flash('success', 'تم حفظ المكان بنجاح.');
        redirect('admin/places.php');
    }
    $place = ['id' => $id, 'name' => $name, 'description' => $desc, 'image' => $image, 'is_active' => $active, 'sort_order' => $order];
}

$places = $pdo->query('SELECT * FROM places ORDER BY sort_order ASC, id DESC')->fetchAll();
admin_header('معالم الغردقة', 'places');
?>
<p class="muted">هذه الصور تظهر في قسم "أماكن تستحق الزيارة" في الصفحة الرئيسية وفي صفحة معالم الغردقة.</p>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="edit-layout">
    <div class="edit-main">
        <section class="panel">
            <?php if ($places): ?>
                <div class="place-cards">
                    <?php foreach ($places as $p): ?>
                        <div class="place-card <?= $p['is_active'] ? '' : 'is-hidden' ?>">
                            <?= img_tag($p['image'], $p['name']) ?>
                            <div class="place-card__body">
                                <strong><?= e($p['name']) ?></strong>
                                <small class="muted"><?= e(excerpt($p['description'], 70)) ?></small>
                            </div>
                            <div class="place-card__actions">
                                <a class="icon-btn" href="<?= e(base_url('admin/places.php?edit=' . (int)$p['id'])) ?>" title="تعديل"><?= icon('edit') ?></a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="icon-btn" title="<?= $p['is_active'] ? 'إخفاء' : 'إظهار' ?>"><?= icon($p['is_active'] ? 'eye' : 'x') ?></button></form>
                                <form method="post" data-confirm="حذف هذا المكان؟"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="icon-btn danger" title="حذف"><?= icon('trash') ?></button></form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty">لا توجد صور بعد. أضف أول مكان من النموذج.</p>
            <?php endif; ?>
        </section>
    </div>
    <aside class="edit-side">
        <form method="post" enctype="multipart/form-data" class="form panel" action="<?= e(base_url('admin/places.php' . ($place['id'] ? '?edit=' . (int)$place['id'] : ''))) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$place['id'] ?>">
            <h2 class="panel__title"><?= $place['id'] ? 'تعديل مكان' : 'إضافة مكان جديد' ?></h2>
            <div class="cover-preview" id="placePreview"><?= $place['image'] ? img_tag($place['image'], '') : '<span class="muted">معاينة الصورة</span>' ?></div>
            <div class="field"><label>الصورة</label><input type="file" name="image" accept="image/*" data-preview="placePreview"></div>
            <div class="field"><label>أو رابط صورة</label><input name="image_url" dir="ltr" placeholder="https://..." value="<?= preg_match('#^https?://#', (string)$place['image']) ? e($place['image']) : '' ?>"></div>
            <div class="field"><label>اسم المكان *</label><input name="name" required value="<?= e($place['name']) ?>" placeholder="مثال: جزيرة الجفتون"></div>
            <div class="field"><label>وصف قصير</label><textarea name="description" rows="3" maxlength="600"><?= e($place['description']) ?></textarea></div>
            <div class="grid-2">
                <div class="field"><label>الترتيب</label><input type="number" name="sort_order" value="<?= (int)$place['sort_order'] ?>"></div>
                <label class="check check--inline"><input type="checkbox" name="is_active" <?= $place['is_active'] ? 'checked' : '' ?>> ظاهر</label>
            </div>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> حفظ</button>
            <?php if ($place['id']): ?><a class="btn btn-light btn-block" href="<?= e(base_url('admin/places.php')) ?>">إلغاء التعديل</a><?php endif; ?>
        </form>
    </aside>
</div>
<?php admin_footer(); ?>
