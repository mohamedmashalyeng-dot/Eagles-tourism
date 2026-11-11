<?php
require __DIR__ . '/includes/bootstrap.php';
start_session();

$errors = [];
$sent = isset($_GET['sent']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!empty($_POST['website'])) {
        redirect('contact.php?sent=1');
    }
    $name  = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $msg   = trim((string)($_POST['message'] ?? ''));
    if (mb_strlen($name) < 2) $errors[] = 'برجاء كتابة الاسم.';
    if (!preg_match('/^[0-9+\s()-]{7,20}$/', $phone)) $errors[] = 'برجاء كتابة رقم هاتف صحيح.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صحيح.';
    if (mb_strlen($msg) < 5) $errors[] = 'برجاء كتابة رسالتك.';
    if (!$errors) {
        db()->prepare('INSERT INTO bookings (trip_id, trip_title, name, phone, email, notes, status, created_at) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(['استفسار عام', mb_substr($name, 0, 120), $phone, mb_substr($email, 0, 150), mb_substr($msg, 0, 3000), 'new', now()]);
        unset($_SESSION['old']);
        redirect('contact.php?sent=1');
    }
    $_SESSION['old'] = $_POST;
}

$pageTitle = 'تواصل معنا';
$activeNav = 'contact';
require __DIR__ . '/includes/header.php';
echo page_banner('تواصل معنا', 'فريقنا جاهز للرد على استفساراتك ومساعدتك في اختيار الرحلة المناسبة');
?>
<section class="section">
    <div class="container contact-layout">
        <div class="contact-info">
            <h2>يسعدنا تواصلك</h2>
            <p class="muted">راسلنا عبر النموذج أو تواصل معنا مباشرة من خلال القنوات التالية.</p>
            <ul class="contact-cards">
                <li><span><?= icon('phone') ?></span><div><small>اتصل بنا</small><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>" dir="ltr"><?= e(setting('phone')) ?></a></div></li>
                <li><span><?= icon('chat') ?></span><div><small>واتساب</small><a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">ابدأ المحادثة الآن</a></div></li>
                <li><span><?= icon('mail') ?></span><div><small>البريد الإلكتروني</small><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li>
                <li><span><?= icon('pin') ?></span><div><small>العنوان</small><p><?= e(setting('address')) ?></p></div></li>
                <li><span><?= icon('clock') ?></span><div><small>مواعيد العمل</small><p><?= e(setting('working_hours')) ?></p></div></li>
            </ul>
        </div>
        <div class="contact-form card">
            <?php if ($sent): ?>
                <div class="book-success">
                    <span class="book-success__icon"><?= icon('check') ?></span>
                    <h3>تم إرسال رسالتك بنجاح</h3>
                    <p>شكراً لتواصلك معنا، سنرد عليك في أقرب وقت.</p>
                    <a class="btn btn-primary" href="<?= e(base_url('trips.php')) ?>">تصفح الرحلات</a>
                </div>
            <?php else: ?>
                <h3>أرسل لنا رسالة</h3>
                <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="grid-2">
                        <div class="field"><label for="cName">الاسم</label><input id="cName" name="name" required value="<?= e(old('name')) ?>"></div>
                        <div class="field"><label for="cPhone">رقم الهاتف</label><input id="cPhone" name="phone" type="tel" dir="ltr" required value="<?= e(old('phone')) ?>"></div>
                    </div>
                    <div class="field"><label for="cEmail">البريد الإلكتروني (اختياري)</label><input id="cEmail" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>"></div>
                    <div class="field"><label for="cMsg">رسالتك</label><textarea id="cMsg" name="message" rows="5" required><?= e(old('message')) ?></textarea></div>
                    <button class="btn btn-primary btn-lg" type="submit">إرسال الرسالة</button>
                </form>
                <?php unset($_SESSION['old']); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <div class="map">
            <iframe title="موقعنا على الخريطة" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q=<?= rawurlencode(setting('map_query', 'Hurghada, Red Sea, Egypt')) ?>&z=13&output=embed"></iframe>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
