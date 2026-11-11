<?php
require __DIR__ . '/_layout.php';
require_admin();
$pdo = db();

$filter = (string)($_GET['status'] ?? '');
if (!isset(BOOKING_STATUSES[$filter])) $filter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'status' && $id && isset(BOOKING_STATUSES[$_POST['status'] ?? ''])) {
        $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
        flash('success', 'تم تحديث حالة الحجز.');
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM bookings WHERE id = ?')->execute([$id]);
        flash('success', 'تم حذف الحجز.');
    }
    redirect('admin/bookings.php' . ($filter ? '?status=' . $filter : ''));
}

$counts = ['' => 0];
foreach ($pdo->query('SELECT status, COUNT(*) c FROM bookings GROUP BY status') as $r) {
    $counts[$r['status']] = (int)$r['c'];
    $counts[''] += (int)$r['c'];
}
if ($filter) {
    $st = $pdo->prepare('SELECT * FROM bookings WHERE status = ? ORDER BY id DESC LIMIT 300');
    $st->execute([$filter]);
} else {
    $st = $pdo->query('SELECT * FROM bookings ORDER BY id DESC LIMIT 300');
}
$bookings = $st->fetchAll();

admin_header('الحجوزات والرسائل', 'bookings');
?>
<div class="tabs">
    <a class="<?= $filter === '' ? 'is-active' : '' ?>" href="<?= e(base_url('admin/bookings.php')) ?>">الكل <em><?= $counts[''] ?></em></a>
    <?php foreach (BOOKING_STATUSES as $k => $l): ?>
        <a class="<?= $filter === $k ? 'is-active' : '' ?>" href="<?= e(base_url('admin/bookings.php?status=' . $k)) ?>"><?= e($l) ?> <em><?= $counts[$k] ?? 0 ?></em></a>
    <?php endforeach; ?>
</div>

<?php if ($bookings): ?>
<div class="booking-list">
    <?php foreach ($bookings as $b):
        $phoneDigits = preg_replace('/\D+/', '', $b['phone']);
        if (str_starts_with($phoneDigits, '0')) $phoneDigits = '2' . $phoneDigits; // Egyptian local → international
        $waText = 'مرحباً ' . $b['name'] . '، معك ' . setting('site_name') . ' بخصوص طلبك: ' . $b['trip_title'] . ($b['travel_date'] ? ' بتاريخ ' . $b['travel_date'] : '');
    ?>
    <article class="booking">
        <div class="booking__head">
            <div>
                <strong><?= e($b['name']) ?></strong>
                <span class="status status-<?= e($b['status']) ?>"><?= e(BOOKING_STATUSES[$b['status']] ?? $b['status']) ?></span>
                <?php if (!$b['trip_id']): ?><span class="tag">رسالة</span><?php endif; ?>
            </div>
            <small class="muted">#<?= (int)$b['id'] ?> · <?= e(date('Y-m-d H:i', strtotime($b['created_at']))) ?></small>
        </div>
        <div class="booking__grid">
            <div><small>الرحلة</small><span><?= e($b['trip_title']) ?></span></div>
            <div><small>الهاتف</small><a href="tel:<?= e($b['phone']) ?>" dir="ltr"><?= e($b['phone']) ?></a></div>
            <?php if ($b['email']): ?><div><small>البريد</small><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></div><?php endif; ?>
            <?php if ($b['travel_date']): ?><div><small>تاريخ الرحلة</small><span><?= e($b['travel_date']) ?></span></div><?php endif; ?>
            <?php if ($b['trip_id']): ?><div><small>الأفراد</small><span><?= (int)$b['adults'] ?> بالغ · <?= (int)$b['children'] ?> طفل</span></div><?php endif; ?>
            <?php if ($b['hotel']): ?><div><small>الفندق</small><span><?= e($b['hotel']) ?></span></div><?php endif; ?>
            <?php if ((float)$b['total'] > 0): ?><div><small>الإجمالي</small><strong><?= e(price($b['total'])) ?></strong></div><?php endif; ?>
        </div>
        <?php if ($b['notes']): ?><p class="booking__notes"><?= nl2br(e($b['notes'])) ?></p><?php endif; ?>
        <div class="booking__actions">
            <a class="btn btn-wa btn-sm" href="https://wa.me/<?= e($phoneDigits) ?>?text=<?= rawurlencode($waText) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> واتساب</a>
            <form method="post" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="action" value="status">
                <select name="status" onchange="this.form.submit()" aria-label="الحالة">
                    <?php foreach (BOOKING_STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $b['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                </select>
            </form>
            <form method="post" data-confirm="حذف هذا الطلب نهائياً؟">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="action" value="delete">
                <button class="icon-btn danger" title="حذف"><?= icon('trash') ?></button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section class="panel"><p class="empty">لا توجد طلبات هنا.</p></section>
<?php endif; ?>
<?php admin_footer(); ?>
