<?php
require __DIR__ . '/_layout.php';
$user = require_admin();
$pdo = db();

$fields = [
    'بيانات الشركة' => [
        'site_name'     => ['اسم الشركة', 'text'],
        'site_tagline'  => ['الشعار المختصر', 'text'],
        'phone'         => ['رقم الهاتف', 'ltr'],
        'whatsapp'      => ['رقم واتساب (بالصيغة الدولية بدون + مثال: 201001234567)', 'ltr'],
        'email'         => ['البريد الإلكتروني', 'ltr'],
        'address'       => ['العنوان', 'text'],
        'working_hours' => ['مواعيد العمل', 'text'],
        'facebook'      => ['رابط فيسبوك', 'ltr'],
        'instagram'     => ['رابط إنستجرام', 'ltr'],
        'currency'      => ['العملة (مثال: ج.م أو $ أو €)', 'text'],
    ],
    'الصفحة الرئيسية' => [
        'hero_title'    => ['العنوان الرئيسي', 'text'],
        'hero_subtitle' => ['النص أسفل العنوان', 'textarea'],
        'about_text'    => ['نبذة "من نحن"', 'textarea'],
        'stat_clients'  => ['عدد العملاء (يظهر في الأرقام)', 'text'],
        'stat_rating'   => ['تقييم العملاء', 'text'],
        'stat_years'    => ['سنوات الخبرة', 'text'],
        'testimonials'  => ['آراء العملاء — كل رأي في سطر بالشكل: الاسم | المدينة | الرأي (اتركه فارغاً لإخفاء القسم)', 'textarea'],
        'booking_policy'=> ['ملاحظات أسفل نموذج الحجز (كل ملاحظة في سطر)', 'textarea'],
        'meta_description' => ['وصف الموقع لمحركات البحث (SEO)', 'textarea'],
    ],
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $errors[] = 'حجم الصورة أكبر من الحد المسموح على الاستضافة.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'settings') {
        foreach ($fields as $group) {
            foreach ($group as $key => $_) {
                save_setting($key, trim((string)($_POST[$key] ?? '')));
            }
        }
        foreach (TRANSLATABLE_SETTINGS as $key) {
            foreach (array_keys(LANGS) as $code) {
                if ($code !== DEFAULT_LANG) {
                    save_setting($key . '__' . $code, trim((string)($_POST['tr'][$code][$key] ?? '')));
                }
            }
        }
        // automatic translation: the key is only replaced when a new one is typed
        $newKey = trim((string)($_POST['google_translate_key'] ?? ''));
        if (isset($_POST['remove_translate_key'])) {
            save_setting('google_translate_key', '');
        } elseif ($newKey !== '') {
            save_setting('google_translate_key', $newKey);
        }
        save_setting('auto_translate', isset($_POST['auto_translate']) ? '1' : '0');
        settings(true); // reload, so the key and switch saved above are used right away
        if (auto_translate_enabled()) {
            $arabic = [];
            foreach (TRANSLATABLE_SETTINGS as $key) {
                $arabic[$key] = trim((string)($_POST[$key] ?? ''));
            }
            auto_translate_flash(fn() => auto_translate_settings($arabic, (array)($_POST['tr'] ?? []), isset($_POST['retranslate'])));
        }
        try {
            $current = setting('hero_image');
            $up = upload_image($_FILES['hero_image'] ?? null, 'site');
            $url = trim((string)($_POST['hero_image_url'] ?? ''));
            if ($up) {
                save_setting('hero_image', $up);
                delete_upload($current);
            } elseif ($url !== '' && preg_match('#^https?://#i', $url) && $url !== $current) {
                save_setting('hero_image', $url);
                delete_upload($current);
            }
            flash('success', 'تم حفظ الإعدادات.');
            redirect('admin/settings.php');
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    } elseif ($action === 'translate_all') {
        if (translate_api_key() === '') {
            $errors[] = 'سجّل مفتاح Google Translate الأول.';
        } else {
            auto_translate_flash(fn() => auto_translate_everything());
            redirect('admin/settings.php#auto-translate');
        }
    } elseif ($action === 'password') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        $hash = (string)$st->fetchColumn();
        $new = (string)($_POST['new_password'] ?? '');
        if (!password_verify((string)($_POST['current_password'] ?? ''), $hash)) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة.';
        } elseif (mb_strlen($new) < 8) {
            $errors[] = 'كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف.';
        } elseif ($new !== (string)($_POST['confirm_password'] ?? '')) {
            $errors[] = 'تأكيد كلمة المرور غير مطابق.';
        } else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'تم تغيير كلمة المرور.');
            redirect('admin/settings.php');
        }
    }
}

$hero = setting('hero_image');
admin_header('الإعدادات', 'settings');
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="edit-layout">
    <form method="post" enctype="multipart/form-data" class="form edit-main">
        <?= csrf_field() ?><input type="hidden" name="action" value="settings">
        <?php foreach ($fields as $groupTitle => $group): ?>
            <section class="panel">
                <h2 class="panel__title"><?= e($groupTitle) ?></h2>
                <div class="grid-2">
                    <?php foreach ($group as $key => [$label, $type]): $val = settings()[$key] ?? ''; ?>
                        <div class="field <?= $type === 'textarea' ? 'span-2' : '' ?>">
                            <label for="s_<?= e($key) ?>"><?= e($label) ?></label>
                            <?php if ($type === 'textarea'): ?>
                                <textarea id="s_<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $key === 'testimonials' ? 5 : 3 ?>"><?= e($val) ?></textarea>
                            <?php else: ?>
                                <input id="s_<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($val) ?>" <?= $type === 'ltr' ? 'dir="ltr"' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        <?php
        $allLabels = array_merge(...array_values($fields));
        $trLabels = [];
        foreach (TRANSLATABLE_SETTINGS as $key) {
            $type = ($allLabels[$key][1] ?? 'text') === 'textarea' ? 'list' : 'input';
            $trLabels[$key] = [$allLabels[$key][0] ?? $key, $type];
        }
        $trValues = [];
        foreach (array_keys(LANGS) as $code) {
            foreach (TRANSLATABLE_SETTINGS as $key) {
                $trValues[$code][$key] = settings()[$key . '__' . $code] ?? '';
            }
        }
        echo translations_panel('ترجمة نصوص الموقع', $trLabels, $trValues, false,
            'لو سبت الخانة فاضية: الموقع بيعرض الترجمة الجاهزة طالما النص العربي زي ما هو، ولو غيّرت النص العربي بيعرض الترجمة الإنجليزي أو العربي.');
        ?>

        <section class="panel" id="auto-translate">
            <h2 class="panel__title">الترجمة التلقائية (Google Translate)</h2>
            <p class="hint">لما تحفظ رحلة أو مكان أو الإعدادات، أي خانة ترجمة فاضية بتتترجم لوحدها من العربي للإنجليزي والألماني والروسي والإسباني. الترجمة اللي إنت كاتبها بإيدك مش بتتغير.</p>
            <?php $hasKey = translate_api_key() !== ''; $keyInConfig = trim((string)config('google_translate_key', '')) !== ''; ?>
            <div class="grid-2">
                <div class="field span-2">
                    <label for="gkey">مفتاح Google Cloud Translation API</label>
                    <?php if ($keyInConfig): ?>
                        <p class="tr-auto is-on">المفتاح متسجل في ملف config.php.</p>
                    <?php else: ?>
                        <input id="gkey" type="password" name="google_translate_key" dir="ltr" autocomplete="off"
                               placeholder="<?= $hasKey ? '•••••••• المفتاح محفوظ — اكتب مفتاح جديد لو عايز تغيّره' : 'AIza...' ?>">
                        <?php if ($hasKey): ?><label class="check"><input type="checkbox" name="remove_translate_key"> امسح المفتاح</label><?php endif; ?>
                    <?php endif; ?>
                </div>
                <label class="check span-2"><input type="checkbox" name="auto_translate" <?= (settings()['auto_translate'] ?? '1') === '1' ? 'checked' : '' ?>> ترجم تلقائياً عند الحفظ</label>
            </div>
        </section>

        <section class="panel">
            <h2 class="panel__title">صورة الواجهة الرئيسية</h2>
            <div class="cover-preview cover-preview--wide" id="heroPreview"><?= img_tag($hero, 'صورة الواجهة') ?></div>
            <div class="grid-2">
                <div class="field"><label>رفع صورة جديدة (يفضل عرض 1920 بكسل)</label><input type="file" name="hero_image" accept="image/*" data-preview="heroPreview"></div>
                <div class="field"><label>أو رابط صورة</label><input name="hero_image_url" dir="ltr" value="<?= preg_match('#^https?://#', $hero) ? e($hero) : '' ?>"></div>
            </div>
        </section>
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('check') ?> حفظ الإعدادات</button>
    </form>

    <aside class="edit-side">
        <?php if (translate_api_key() !== ''): ?>
        <form method="post" class="form panel" data-confirm="هيترجم كل الخانات الفاضية في كل الرحلات والمعالم والإعدادات. ممكن ياخد دقيقة. تكمل؟">
            <?= csrf_field() ?><input type="hidden" name="action" value="translate_all">
            <h2 class="panel__title">ترجمة كل المحتوى</h2>
            <p class="hint">بيترجم كل الخانات الفاضية في كل الرحلات والمعالم والإعدادات مرة واحدة. الترجمات الموجودة مش بتتغير.</p>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('globe') ?> ترجم الآن</button>
        </form>
        <?php endif; ?>
        <form method="post" class="form panel">
            <?= csrf_field() ?><input type="hidden" name="action" value="password">
            <h2 class="panel__title">تغيير كلمة المرور</h2>
            <div class="field"><label>كلمة المرور الحالية</label><input type="password" name="current_password" dir="ltr" required autocomplete="current-password"></div>
            <div class="field"><label>كلمة المرور الجديدة</label><input type="password" name="new_password" dir="ltr" required minlength="8" autocomplete="new-password"></div>
            <div class="field"><label>تأكيد كلمة المرور</label><input type="password" name="confirm_password" dir="ltr" required minlength="8" autocomplete="new-password"></div>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('lock') ?> تغيير</button>
        </form>
    </aside>
</div>
<?php admin_footer(); ?>
