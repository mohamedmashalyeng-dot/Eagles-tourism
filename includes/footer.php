</main>

<footer class="site-footer">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0 30c120 20 240 30 360 22S600 10 720 10s240 30 360 34 240-12 360-22v38H0z" fill="currentColor"/></svg>
    </div>
    <div class="container footer-grid">
        <div class="footer-col footer-about">
            <a href="<?= e(base_url()) ?>" class="brand brand--light">
                <?= brand_mark() ?>
                <span class="brand__text"><strong>EAGLES</strong><small>TOURISM</small></span>
            </a>
            <p><?= e(excerpt(setting('about_text'), 180)) ?></p>
            <div class="socials">
                <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener" aria-label="فيسبوك"><?= icon('facebook') ?></a><?php endif; ?>
                <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" aria-label="إنستجرام"><?= icon('instagram') ?></a><?php endif; ?>
                <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" aria-label="واتساب"><?= icon('chat') ?></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>روابط سريعة</h4>
            <ul>
                <li><a href="<?= e(base_url('trips.php')) ?>">كل الرحلات</a></li>
                <li><a href="<?= e(base_url('gallery.php')) ?>">معالم الغردقة</a></li>
                <li><a href="<?= e(base_url('index.php#about')) ?>">من نحن</a></li>
                <li><a href="<?= e(base_url('contact.php')) ?>">تواصل معنا</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>أنواع الرحلات</h4>
            <ul>
                <?php foreach (TRIP_CATEGORIES as $k => $label): ?>
                    <li><a href="<?= e(base_url('trips.php?cat=' . $k)) ?>"><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h4>تواصل معنا</h4>
            <ul class="contact-list">
                <li><?= icon('pin') ?><span><?= e(setting('address')) ?></span></li>
                <li><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>" dir="ltr"><?= e(setting('phone')) ?></a></li>
                <li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                <li><?= icon('clock') ?><span><?= e(setting('working_hours')) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?> — جميع الحقوق محفوظة</span>
            <span>الغردقة · البحر الأحمر · مصر</span>
        </div>
    </div>
</footer>

<a href="<?= e(whatsapp_link('مرحباً، أريد الاستفسار عن رحلاتكم')) ?>" class="wa-float" target="_blank" rel="noopener" aria-label="تواصل عبر واتساب">
    <?= icon('chat') ?><span>واتساب</span>
</a>

<div class="lightbox" id="lightbox" data-placeholder="<?= e(base_url('assets/img/placeholder.svg')) ?>" hidden>
    <button class="lightbox__close" aria-label="إغلاق"><?= icon('x') ?></button>
    <button class="lightbox__nav lightbox__prev" aria-label="السابق"><?= icon('arrow') ?></button>
    <figure><img alt=""><figcaption></figcaption></figure>
    <button class="lightbox__nav lightbox__next" aria-label="التالي"><?= icon('arrow') ?></button>
</div>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
