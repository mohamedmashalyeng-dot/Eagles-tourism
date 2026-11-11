<?php
require __DIR__ . '/_layout.php';

$pdo = db();
$stats = [
    ['الرحلات المنشورة', (int)$pdo->query('SELECT COUNT(*) FROM trips WHERE is_active = 1')->fetchColumn(), 'compass', 'trips.php'],
    ['حجوزات جديدة', (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn(), 'inbox', 'bookings.php?status=new'],
    ['حجوزات مؤكدة', (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(), 'check', 'bookings.php?status=confirmed'],
    ['صور المعالم', (int)$pdo->query('SELECT COUNT(*) FROM places')->fetchColumn(), 'image', 'places.php'],
];
$latest = $pdo->query('SELECT * FROM bookings ORDER BY id DESC LIMIT 8')->fetchAll();

admin_header('الرئيسية', 'dashboard');
?>
<div class="stat-grid">
    <?php foreach ($stats as [$label, $value, $ic, $href]): ?>
        <a class="stat-card" href="<?= e(base_url('admin/' . $href)) ?>">
            <span class="stat-card__icon"><?= icon($ic) ?></span>
            <div><strong><?= $value ?></strong><small><?= e($label) ?></small></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="quick-actions">
    <a class="btn btn-primary" href="<?= e(base_url('admin/trip-edit.php')) ?>"><?= icon('plus') ?> إضافة رحلة جديدة</a>
    <a class="btn btn-light" href="<?= e(base_url('admin/places.php')) ?>"><?= icon('image') ?> إضافة صورة معلم</a>
    <a class="btn btn-light" href="<?= e(base_url('admin/settings.php')) ?>"><?= icon('sliders') ?> بيانات التواصل</a>
</div>

<section class="panel">
    <div class="panel__head"><h2>أحدث الحجوزات والرسائل</h2><a href="<?= e(base_url('admin/bookings.php')) ?>">عرض الكل</a></div>
    <?php if ($latest): ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>العميل</th><th>الرحلة</th><th>التاريخ</th><th>الأفراد</th><th>الإجمالي</th><th>الحالة</th></tr></thead>
                <tbody>
                <?php foreach ($latest as $b): ?>
                    <tr>
                        <td><strong><?= e($b['name']) ?></strong><br><a class="muted" dir="ltr" href="tel:<?= e($b['phone']) ?>"><?= e($b['phone']) ?></a></td>
                        <td><?= e($b['trip_title']) ?></td>
                        <td><?= e($b['travel_date'] ?: '—') ?></td>
                        <td><?= $b['trip_id'] ? (int)$b['adults'] . ' + ' . (int)$b['children'] : '—' ?></td>
                        <td><?= (float)$b['total'] > 0 ? e(price($b['total'])) : '—' ?></td>
                        <td><span class="status status-<?= e($b['status']) ?>"><?= e(BOOKING_STATUSES[$b['status']] ?? $b['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">لا توجد حجوزات حتى الآن.</p>
    <?php endif; ?>
</section>
<?php admin_footer(); ?>
