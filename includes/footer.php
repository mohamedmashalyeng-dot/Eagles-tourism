</main>

<footer class="site-footer">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none"><path d="M0 30c120 20 240 30 360 22S600 10 720 10s240 30 360 34 240-12 360-22v38H0z" fill="currentColor"/></svg>
    </div>
    <div class="container footer-grid">
        <div class="footer-col footer-about">
            <a href="<?= e(url()) ?>" class="brand brand--light">
                <?= brand_mark() ?>
                <span class="brand__text"><strong>EAGLES</strong><small>TOURISM</small></span>
            </a>
            <p><?= e(excerpt(setting('about_text'), 180)) ?></p>
            <div class="socials">
                <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener" aria-label="<?= e(t('فيسبوك')) ?>"><?= icon('facebook') ?></a><?php endif; ?>
                <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" aria-label="<?= e(t('إنستجرام')) ?>"><?= icon('instagram') ?></a><?php endif; ?>
                <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" aria-label="<?= e(t('واتساب')) ?>"><?= icon('chat') ?></a>
            </div>
        </div>
        <div class="footer-col">
            <h4><?= e(t('روابط سريعة')) ?></h4>
            <ul>
                <li><a href="<?= e(url('trips.php')) ?>"><?= e(t('كل الرحلات')) ?></a></li>
                <li><a href="<?= e(url('gallery.php')) ?>"><?= e(t('معالم الغردقة')) ?></a></li>
                <li><a href="<?= e(url('index.php#about')) ?>"><?= e(t('من نحن')) ?></a></li>
                <li><a href="<?= e(url('contact.php')) ?>"><?= e(t('تواصل معنا')) ?></a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4><?= e(t('أنواع الرحلات')) ?></h4>
            <ul>
                <?php foreach (array_keys(TRIP_CATEGORIES) as $k): ?>
                    <li><a href="<?= e(url('trips.php?cat=' . $k)) ?>"><?= e(category_label($k)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h4><?= e(t('تواصل معنا')) ?></h4>
            <ul class="contact-list">
                <?php foreach (business_locations() as $location): ?>
                <li><?= icon('pin') ?><a href="<?= e(url('contact.php#locations')) ?>"><?= e($location['name']) ?> — <?= e(t('الأحياء')) ?></a></li>
                <?php endforeach; ?>
                <li><?= icon('phone') ?><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>" dir="ltr"><?= e(setting('phone')) ?></a></li>
                <?php if (setting('email')): ?><li><?= icon('mail') ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li><?php endif; ?>
                <?php if (setting('working_hours')): ?><li><?= icon('clock') ?><span><?= e(setting('working_hours')) ?></span></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?> — <?= e(t('جميع الحقوق محفوظة')) ?></span>
            <span><?= e(t('الغردقة · البحر الأحمر · مصر')) ?></span>
        </div>
    </div>
</footer>

<a href="<?= e(whatsapp_link(t('مرحباً، أريد الاستفسار عن رحلاتكم'))) ?>" class="wa-float" target="_blank" rel="noopener" aria-label="<?= e(t('تواصل عبر واتساب')) ?>">
    <?= icon('chat') ?><span><?= e(t('واتساب')) ?></span>
</a>

<div class="lightbox" id="lightbox" data-placeholder="<?= e(base_url('assets/img/placeholder.svg')) ?>" hidden>
    <button class="lightbox__close" aria-label="<?= e(t('إغلاق')) ?>"><?= icon('x') ?></button>
    <button class="lightbox__nav lightbox__prev" aria-label="<?= e(t('السابق')) ?>"><?= icon('arrow') ?></button>
    <figure><img alt=""><figcaption></figcaption></figure>
    <button class="lightbox__nav lightbox__next" aria-label="<?= e(t('التالي')) ?>"><?= icon('arrow') ?></button>
</div>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
