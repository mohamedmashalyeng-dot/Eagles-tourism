<?php
require __DIR__ . '/includes/bootstrap.php';
start_session();

$errors = [];
$sent = isset($_GET['sent']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!empty($_POST['website'])) {
        redirect(url('contact.php?sent=1'));
    }
    $name  = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $msg   = trim((string)($_POST['message'] ?? ''));
    if (mb_strlen($name) < 2) $errors[] = t('برجاء كتابة الاسم.');
    if (!preg_match('/^[0-9+\s()-]{7,20}$/', $phone)) $errors[] = t('برجاء كتابة رقم هاتف صحيح.');
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('البريد الإلكتروني غير صحيح.');
    if (mb_strlen($msg) < 5) $errors[] = t('برجاء كتابة رسالتك.');
    if (!$errors) {
        // the dashboard is Arabic: note the visitor's language so the team replies in it
        if (lang() !== DEFAULT_LANG) {
            $msg = '[لغة العميل: ' . LANGS[lang()]['name'] . "]\n" . $msg;
        }
        db()->prepare('INSERT INTO bookings (trip_id, trip_title, name, phone, email, notes, status, created_at) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(['استفسار عام', mb_substr($name, 0, 120), $phone, mb_substr($email, 0, 150), mb_substr($msg, 0, 3000), 'new', now()]);
        unset($_SESSION['old']);
        redirect(url('contact.php?sent=1'));
    }
    $_SESSION['old'] = $_POST;
}

$pageTitle = t('تواصل معنا');
$activeNav = 'contact';
require __DIR__ . '/includes/header.php';
echo page_banner($pageTitle, t('فريقنا جاهز للرد على استفساراتك ومساعدتك في اختيار الرحلة المناسبة'));
?>
<section class="section">
    <div class="container contact-layout">
        <div class="contact-info">
            <h2><?= e(t('يسعدنا تواصلك')) ?></h2>
            <p class="muted"><?= e(t('راسلنا عبر النموذج أو تواصل معنا مباشرة من خلال القنوات التالية.')) ?></p>
            <ul class="contact-cards">
                <li><span><?= icon('phone') ?></span><div><small><?= e(t('اتصل بنا')) ?></small><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>" dir="ltr"><?= e(setting('phone')) ?></a></div></li>
                <li><span><?= icon('chat') ?></span><div><small><?= e(t('واتساب')) ?></small><a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><?= e(t('ابدأ المحادثة الآن')) ?></a></div></li>
                <?php if (setting('email')): ?><li><span><?= icon('mail') ?></span><div><small><?= e(t('البريد الإلكتروني')) ?></small><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li><?php endif; ?>
                <li><span><?= icon('pin') ?></span><div><small><?= e(t('العنوان')) ?></small><p><?= e(setting('address')) ?></p></div></li>
                <?php if (setting('working_hours')): ?><li><span><?= icon('clock') ?></span><div><small><?= e(t('مواعيد العمل')) ?></small><p><?= e(setting('working_hours')) ?></p></div></li><?php endif; ?>
            </ul>
        </div>
        <div class="contact-form card">
            <?php if ($sent): ?>
                <div class="book-success">
                    <span class="book-success__icon"><?= icon('check') ?></span>
                    <h3><?= e(t('تم إرسال رسالتك بنجاح')) ?></h3>
                    <p><?= e(t('شكراً لتواصلك معنا، سنرد عليك في أقرب وقت.')) ?></p>
                    <a class="btn btn-primary" href="<?= e(url('trips.php')) ?>"><?= e(t('تصفح الرحلات')) ?></a>
                </div>
            <?php else: ?>
                <h3><?= e(t('أرسل لنا رسالة')) ?></h3>
                <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
                <form method="post" class="form" action="<?= e(url('contact.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="grid-2">
                        <div class="field"><label for="cName"><?= e(t('الاسم')) ?></label><input id="cName" name="name" required value="<?= e(old('name')) ?>"></div>
                        <div class="field"><label for="cPhone"><?= e(t('رقم الهاتف')) ?></label><input id="cPhone" name="phone" type="tel" dir="ltr" required value="<?= e(old('phone')) ?>"></div>
                    </div>
                    <div class="field"><label for="cEmail"><?= e(t('البريد الإلكتروني (اختياري)')) ?></label><input id="cEmail" name="email" type="email" dir="ltr" value="<?= e(old('email')) ?>"></div>
                    <div class="field"><label for="cMsg"><?= e(t('رسالتك')) ?></label><textarea id="cMsg" name="message" rows="5" required><?= e(old('message')) ?></textarea></div>
                    <button class="btn btn-primary btn-lg" type="submit"><?= e(t('إرسال الرسالة')) ?></button>
                </form>
                <?php unset($_SESSION['old']); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= locations_section() ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
