<?php
require __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete' && $id) {
        $st = $pdo->prepare('SELECT cover_image FROM trips WHERE id = ?');
        $st->execute([$id]);
        $cover = $st->fetchColumn();
        $imgs = $pdo->prepare('SELECT path FROM trip_images WHERE trip_id = ?');
        $imgs->execute([$id]);
        $paths = $imgs->fetchAll(PDO::FETCH_COLUMN);
        $pdo->prepare('DELETE FROM trip_images WHERE trip_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM trips WHERE id = ?')->execute([$id]);
        delete_upload($cover ?: null);
        foreach ($paths as $p) delete_upload($p);
        flash('success', 'تم حذف الرحلة.');
    } elseif ($action === 'toggle_active' && $id) {
        $pdo->prepare('UPDATE trips SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('success', 'تم تحديث حالة النشر.');
    } elseif ($action === 'toggle_featured' && $id) {
        $pdo->prepare('UPDATE trips SET is_featured = 1 - is_featured WHERE id = ?')->execute([$id]);
        flash('success', 'تم التحديث.');
    }
    redirect('admin/trips.php');
}

$trips = $pdo->query('SELECT t.*, (SELECT COUNT(*) FROM trip_images i WHERE i.trip_id = t.id) AS img_count FROM trips t ORDER BY t.sort_order ASC, t.id DESC')->fetchAll();

admin_header('الرحلات', 'trips');
?>
<div class="page-actions">
    <p class="muted">أضف رحلاتك وحدد سعرها وصورها وتفاصيلها، وستظهر فوراً على الموقع.</p>
    <a class="btn btn-primary" href="<?= e(base_url('admin/trip-edit.php')) ?>"><?= icon('plus') ?> إضافة رحلة</a>
</div>

<section class="panel">
    <?php if ($trips): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>الرحلة</th><th>النوع</th><th>السعر</th><th>الصور</th><th>مميزة</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($trips as $t): ?>
                <tr>
                    <td>
                        <div class="row-media">
                            <?= img_tag($t['cover_image'], $t['title'], 'thumb') ?>
                            <div><strong><?= e($t['title']) ?></strong><small class="muted"><?= e($t['duration']) ?></small></div>
                        </div>
                    </td>
                    <td><?= e(category_label($t['category'])) ?></td>
                    <td><strong><?= e(price($t['price_adult'])) ?></strong><?php if ((float)$t['price_child'] > 0): ?><br><small class="muted">طفل: <?= e(price($t['price_child'])) ?></small><?php endif; ?></td>
                    <td><?= (int)$t['img_count'] + ($t['cover_image'] ? 1 : 0) ?></td>
                    <td>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><input type="hidden" name="action" value="toggle_featured">
                            <button class="star-btn <?= $t['is_featured'] ? 'is-on' : '' ?>" title="تمييز في الصفحة الرئيسية"><?= icon('star') ?></button></form>
                    </td>
                    <td>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><input type="hidden" name="action" value="toggle_active">
                            <button class="switch <?= $t['is_active'] ? 'is-on' : '' ?>" title="نشر / إخفاء"><span></span><?= $t['is_active'] ? 'منشورة' : 'مخفية' ?></button></form>
                    </td>
                    <td><div class="actions">
                        <a class="icon-btn" href="<?= e(base_url('trip.php?id=' . (int)$t['id'])) ?>" target="_blank" title="عرض"><?= icon('eye') ?></a>
                        <a class="icon-btn" href="<?= e(base_url('admin/trip-edit.php?id=' . (int)$t['id'])) ?>" title="تعديل"><?= icon('edit') ?></a>
                        <form method="post" data-confirm="هل أنت متأكد من حذف هذه الرحلة نهائياً؟"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="icon-btn danger" title="حذف"><?= icon('trash') ?></button></form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty"><p>لا توجد رحلات بعد.</p><a class="btn btn-primary" href="<?= e(base_url('admin/trip-edit.php')) ?>">أضف أول رحلة</a></div>
    <?php endif; ?>
</section>
<?php admin_footer(); ?>
