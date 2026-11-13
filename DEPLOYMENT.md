# رفع موقع Eagles Tours على hosting.com (لوحة Enhance)

الدليل ده بيشرح رفع الموقع على `https://eaglestours.com` خطوة بخطوة.
أسماء القوايم مكتوبة بالإنجليزي زي ما بتظهر في Enhance بالظبط.

---

## 1. الموقع مبني بإيه

| الجزء | التقنية |
|---|---|
| الموقع ولوحة التحكم | **PHP عادي** من غير framework ومن غير Composer |
| قاعدة البيانات | **MySQL / MariaDB** عن طريق PDO (فيه SQLite للتجربة على الجهاز بس) |
| الواجهة | HTML + CSS + JavaScript عادي، ومفيش build step |
| خدمات في الخلفية | **ولا حاجة**: مفيش Node.js ولا Python ولا cron ولا queue |

**مهم:** في Enhance خلّي **Persistent runtime (worker process)** على **None**،
وما تعملش أي **worker process** للموقع.

---

## 2. متطلبات السيرفر

| المطلوب | القيمة |
|---|---|
| PHP | **8.3** (أقل نسخة بتشتغل 8.1) |
| قاعدة البيانات | MySQL 5.7+ أو MariaDB 10.3+ بترميز `utf8mb4` |
| إضافات PHP **لازم** تكون موجودة | `pdo_mysql` · `mbstring` |
| إضافات PHP **مستحسنة** | `gd` بدعم JPEG وPNG وWebP، عشان تصغير الصور الكبيرة |
| سيرفر الويب | Apache أو LiteSpeed (بيقروا `.htaccess`). لو Nginx شوف [القسم 7](#nginx) |

**إعدادات PHP المقترحة** (من **Advanced**، جنب **PHP version**):

```
upload_max_filesize = 16M
post_max_size       = 64M
max_file_uploads    = 20
display_errors      = Off
```

مش محتاج تظبط `log_errors` ولا `error_log`. الموقع بيفعّلهم لوحده وبيكتب الأخطاء في ملف خاص (القسم 6).

---

## 3. الملفات اللي هترفعها

ارفع ملف **`eagles-tourism-upload.zip`** وفكّه جوه `public_html`.

**شكل الملفات على السيرفر بعد التثبيت:**

```
/  (فولدر الموقع في Enhance)
├── eagles-private/          ← بيتعمل لوحده وقت التثبيت، وهو برّه public_html ومحدش يقدر يفتحه من النت
│   ├── config.php           ← بيانات قاعدة البيانات (صلاحيات 0600)
│   ├── install.key          ← مفتاح التثبيت، وبيتمسح لوحده بعد ما التثبيت ينجح
│   └── logs/php-error.log   ← سجل أخطاء PHP
└── public_html/
    ├── .htaccess            ← تحويل HTTPS ومنع الملفات الحساسة (ملف مخفي)
    ├── index.php  trips.php  trip.php  gallery.php  contact.php
    ├── install.php          ← امسحه بعد التثبيت
    ├── config.sample.php    ← مثال بس، والوصول له ممنوع
    ├── admin/  assets/
    ├── includes/            ← الوصول ممنوع
    ├── data/                ← الوصول ممنوع، وفيه installed.lock
    └── uploads/trips|places|site/   ← تشغيل السكريبتات ممنوع
```

> لو الاستضافة ما سمحتش بإنشاء فولدرات برّه `public_html`، الموقع بيتصرّف لوحده:
> بيحط `config.php` جوه `public_html` (محمي من الوصول)، والمفتاح جوه `public_html/data/install-<random>.key`،
> والـ logs في `public_html/data/logs/`. وفي الحالتين الوصول ليهم من النت ممنوع.

**ما ترفعش الملفات دي أبداً:**
- `config.php` اللي على جهازك.
- أي حاجة جوه `data/` على جهازك (`eagles.sqlite` و`installed.lock` و`logs/`).
- `.git/` و`README.md` و`DEPLOYMENT.md`.

---

## 4. خطوات الرفع على Enhance

### الخطوة 0: اتأكد إن مفيش worker شغّال
افتح الموقع في Enhance، ولو فيه **worker process** اعمله **Stop** وبعدين **Delete**.

### الخطوة 1: نسخة PHP
افتح **Advanced** واختار **PHP version**، وخليها **8.3**. ولو ينفع، حط إعدادات الرفع اللي في القسم 2.

### الخطوة 2: قاعدة البيانات
1. افتح تاب **Databases** ودوس **Create database**.
2. اعمل مستخدم بكلمة مرور قوية وإدّيله **كل الصلاحيات** على القاعدة دي.
3. اكتب عندك 4 حاجات زي ما Enhance كاتبهم بالظبط: **Host** (غالباً `localhost`)، و**Database name**، و**Username**، و**Password**.

مفيش ملف SQL محتاج تستورده. **`install.php` هو اللي بيعمل الجداول السبعة:**
`users`, `settings`, `trips`, `trip_images`, `places`, `bookings`, `login_attempts`
بـ `InnoDB` و`utf8mb4_unicode_ci`. **لازم القاعدة تكون فاضية**، لأن التثبيت بيرفض أي قاعدة فيها حساب أدمن.

### الخطوة 3: شهادة SSL
افتح **Advanced** ثم **Security** ثم **SSL certificates**، واتأكد إن الشهادة **Let's Encrypt** مش **Self signed**.
**Force HTTPS** ممكن يفضل شغّال، ومش هيعمل redirect loop مع الـ `.htaccess` (القسم 6).

### الخطوة 4: رفع الملفات
1. افتح تاب **Files** وادخل `public_html`، وامسح الصفحة الافتراضية لو موجودة (بعد ما تبص عليها).
2. ارفع `eagles-tourism-upload.zip` واعمله **Extract** جوه `public_html`، وبعدين امسح الـ ZIP.
3. اتأكد إن `index.php` موجود **مباشرة** جوه `public_html`.
4. اتأكد إن ملفات `.htaccess` موجودة: واحد في الـ root، وواحد في كل فولدر من `includes/` و`data/` و`uploads/`. لو مش ظاهرين فعّل عرض الملفات المخفية.

**الصلاحيات:** فولدرات `755` وملفات `644`، و**ما تستخدمش `777`**.
PHP على Enhance بيشتغل بنفس مستخدم الموقع، فهيقدر يكتب في `eagles-private/` و`data/` و`uploads/` من غير أي إعداد.

### الخطوة 5: التثبيت بمفتاح سري
1. افتح `https://eaglestours.com/install.php`.
   - فتح الصفحة بيعمل **مفتاح تثبيت عشوائي**، وبيحفظه في ملف ما بيبانش على أي صفحة.
   - من غير المفتاح ده، الموقع **بيرفض أي محاولة تثبيت** قبل ما يكلّم قاعدة البيانات أو يعمل أي ملف.
2. من **Files** افتح الملف وانسخ المفتاح:
   - **`eagles-private/install.key`**: موجود في الفولدر اللي فيه `public_html`، **مش جوّاه**.
   - أو لو مش موجود هناك: `public_html/data/install-xxxxxxxx.key`.
3. ارجع لصفحة التثبيت واملا البيانات:

| الخانة | تكتب فيها |
|---|---|
| مفتاح التثبيت | المفتاح اللي نسخته |
| نوع قاعدة البيانات | **MySQL / MariaDB** (ما تختارش SQLite على السيرفر) |
| Host / اسم القاعدة / المستخدم / كلمة المرور | من الخطوة 2 |
| اسم مستخدم الأدمن | اسم مش سهل تخمينه، ويفضّل ما يكونش `admin` |
| كلمة مرور الأدمن | 12 حرف أو أكتر، فيها حروف وأرقام ورموز |
| رحلات تجريبية | علّم عليها لو عايز بيانات تجريبية تعدّل عليها |

4. دوس **تثبيت الموقع**. اللي بيحصل:
   - بيتعمل `eagles-private/config.php` و`data/installed.lock`.
   - **المفتاح بيتمسح**، وما ينفعش يتستخدم تاني.
   - **التثبيت بيتقفل نهائياً**: أي فتح لـ `install.php` بعد كده بيرجّع **403**، ومش بيعمل أي حاجة حتى لو معاه مفتاح.
   - لو حصل خطأ في النص، أي حاجة اتسجّلت بتترجع: الأدمن والبيانات التجريبية بيتمسحوا، وتقدر تجرّب تاني بنفس المفتاح.

### الخطوة 6: امسح `install.php`
التثبيت مقفول بالفعل، بس كأمان زيادة: من **Files** امسح `public_html/install.php`.

### الخطوة 7: إعداد الموقع من لوحة التحكم
ادخل `https://eaglestours.com/admin`:
1. **الإعدادات:** اسم الشركة، التليفون، **واتساب بالصيغة الدولية** (مثلاً `201001234567`)، الإيميل، العنوان، ولينكات السوشيال.
2. **الرحلات:** عدّل الرحلات التجريبية أو امسحها، وارفع صورك.
3. **معالم الغردقة:** حط صور حقيقية بدل صور Unsplash.
4. **الإحصائيات وآراء العملاء:** حط بياناتك الحقيقية.

---

## 5. اختبار الموقع بعد الرفع

- [ ] `http://eaglestours.com` بيتحوّل لـ `https://eaglestours.com` بتحويل واحد (301)، من غير loop.
- [ ] الصفحات دي بتفتح عادي: الرئيسية، `/trips.php`، `/trip.php?id=1`، `/gallery.php`، `/contact.php`.
- [ ] الروابط دي بترجع **403 Forbidden**:
  `/config.php` · `/config.sample.php` · `/includes/bootstrap.php` · `/data/installed.lock` · `/data/logs/php-error.log`
- [ ] `/install.php` بيرجع **404** (اتمسح) أو **403** (لو لسه موجود).
- [ ] حجز تجريبي من صفحة رحلة بيظهر في **الحجوزات**.
- [ ] رسالة تجريبية من **تواصل معنا** بتظهر في **الحجوزات** بعلامة "رسالة".
- [ ] رفع صورة لرحلة بيشتغل، والصورة بتظهر في الموقع.
- [ ] تسجيل الدخول والخروج بيشتغلوا، و`/admin` بيحوّل على صفحة الدخول لو مش مسجّل.
- [ ] مفيش أي رسالة خطأ PHP ظاهرة في أي صفحة.

بعد كده امسح الحجز والرسالة التجريبيين من لوحة التحكم.

---

## 6. ملاحظات الأمان

### بيانات قاعدة البيانات
- الموقع بيقرا الإعدادات من `../eagles-private/config.php` الأول، وبعدين من `public_html/config.php`.
- التثبيت بيحط الملف **برّه `public_html`** لو الاستضافة سامحة بكده، وعلى Enhance هي سامحة.
- الملف PHP بيرجّع array ومش بيطبع أي حاجة، و`.htaccess` كمان بيمنع أي `config.php*` من الوصول.
- `'debug' => false` على السيرفر، و**ما تغيّرهاش أبداً**.

### تسجيل الأخطاء
- رسائل الأخطاء **ما بتظهرش للزوار أبداً** (`display_errors=0`)، وكل حاجة بتتسجّل.
- مكان السجل: `eagles-private/logs/php-error.log`. ولو مش متاح: `public_html/data/logs/php-error.log` (الوصول ممنوع).
- `zend.exception_ignore_args=1` مع `#[SensitiveParameter]` معناهم إن الـ stack traces **ما بتكتبش قيم الـ arguments**.
  يعني كلمات المرور وبيانات الاتصال ما بتتكتبش في السجل. الكود كمان ما بيسجّلش session ولا CSRF tokens.
- تقدر تقرا السجل من **Files**.

### حماية تسجيل الدخول
- محاولات الدخول الغلط **متسجّلة في قاعدة البيانات** (جدول `login_attempts`)، فمسح الـ cookies ما بيفيدش.
- الدخول بيتقفل **15 دقيقة** في حالتين:
  - **5 محاولات غلط من نفس الـ IP** خلال 15 دقيقة.
  - **10 محاولات غلط على نفس اسم المستخدم** من أي IPs خلال 15 دقيقة.
- القفل **مؤقت**: بيتفك لوحده بعد 15 دقيقة من آخر محاولات، والدخول الصح بيمسح العدّاد.
- الـ IP بيتاخد من `REMOTE_ADDR` بس، لأن الـ headers اللي زي `X-Forwarded-For` ممكن تتزوّر.
  لو حطيت Cloudflare قدام الموقع، كل الزوار هيظهروا بـ IPs بتاعة Cloudflare. اطلب من الدعم تفعيل `mod_remoteip`.

### الحماية الموجودة من الأول
bcrypt، و`session_regenerate_id`، وcookies بـ `HttpOnly` و`SameSite=Lax` و`Secure`، وCSRF على كل فورم،
وprepared statements في كل الاستعلامات، وescaping لكل اللي بيتعرض، وفحص حقيقي لنوع الصور المرفوعة، وhoneypot ضد السبام.

### تحويل HTTPS من غير redirect loop
التحويل بيحصل **بس لو** `HTTPS` مقفول **و** `X-Forwarded-Proto` مش `https`.
فـ **Force HTTPS** في Enhance مع `.htaccess` مع Cloudflare (بوضع **Full** أو **Full (strict)**) مش هيعملوا loop.

### ما تشغّلش page caching
الصفحات فيها CSRF token مختلف لكل زائر. كاش الصفحات الكاملة (LiteSpeed Cache أو Cloudflare "Cache Everything")
هيطلّع للزوار **"انتهت صلاحية الجلسة"** لما يبعتوا الفورم.

---

<a id="nginx"></a>
## 7. لو السيرفر Nginx من غير Apache

Nginx بيتجاهل `.htaccess`. بيانات قاعدة البيانات والسجل والمفتاح كلهم برّه `public_html` فمش متأثرين،
بس اطلب من الدعم يضيفوا القواعد دي، بالإضافة لتحويل HTTPS:
```nginx
location ~ ^/(includes|data)/ { deny all; }
location ~ /(config\.php.*|config\.sample\.php|README\.md|DEPLOYMENT\.md)$ { deny all; }
location ~ \.(sqlite|sqlite3|db|sql|key|log|lock|bak|old|orig|swp|zip|tar|gz)$ { deny all; }
location ~ ^/uploads/.*\.(php\d?|phtml|phar)$ { deny all; }
location ~ /\.(git|svn|hg) { return 404; }
```

---

## 8. حل المشاكل

| المشكلة | السبب الغالب والحل |
|---|---|
| **500 Internal Server Error** على طول بعد الرفع | السيرفر رافض `Options -Indexes`. حط `#` قبل سطر `Options` في `.htaccess` وفي `uploads/.htaccess` |
| **ERR_TOO_MANY_REDIRECTS** | proxy مش بيبعت `X-Forwarded-Proto`. اقفل Force HTTPS في واحد من الاتنين، أو اسأل الدعم |
| **"مفتاح التثبيت غير صحيح"** | انسخ محتوى `install.key` كامل، من غير مسافات زيادة |
| **"تعذر إنشاء ملف مفتاح التثبيت"** | `public_html/data/` مش قابل للكتابة. خلّي صلاحياته `755` |
| **"قاعدة البيانات تحتوي على حساب مدير بالفعل"** | القاعدة مش فاضية. امسح جداولها من phpMyAdmin، أو اعمل قاعدة جديدة |
| **"تعذر الاتصال بقاعدة البيانات"** | Host أو الاسم أو المستخدم غلط، أو المستخدم مش مضاف للقاعدة |
| الموقع بيحوّل على `install.php` | ملف الإعدادات أو `data/installed.lock` مش موجود |
| `install.php` بيرجّع 403 والموقع **مش** مثبّت | فيه `config.php` قديم (في `eagles-private/` أو `public_html/`). امسحه وافتح `install.php` تاني |
| **"انتهت صلاحية الجلسة"** في الفورمات | page caching شغّال، أو الـ cookies مقفولة في المتصفح |
| **"حجم الصور أكبر من المسموح"** | زوّد `upload_max_filesize` و`post_max_size`، أو ارفع صور أقل في المرة الواحدة |
| **"محاولات كثيرة خاطئة"** وإنت صاحب الحساب | استنى 15 دقيقة، أو من phpMyAdmin شغّل: `DELETE FROM login_attempts;` |

### نسيت كلمة مرور الأدمن
التثبيت مقفول نهائياً، فاستعادة كلمة المرور بتتعمل من قاعدة البيانات:
1. من تاب **Databases** افتح **phpMyAdmin** واختار قاعدة الموقع.
2. شغّل الأمر ده. هو بيحط كلمة مرور مؤقتة: `Eagles-Reset-2026!`
   ```sql
   UPDATE users SET password_hash = '$2y$10$Qaekubt/NkzEOUAOVBpM/ecMndpzEgpbzztNwSPfY.eT6zAvfc/HK'
   WHERE username = 'اسم_المستخدم_بتاعك';
   DELETE FROM login_attempts;
   ```
3. ادخل بكلمة المرور المؤقتة، و**غيّرها على طول** من **الإعدادات** ثم **تغيير كلمة المرور**.

---

## 9. النسخ الاحتياطي والتحديثات

- **النسخ الاحتياطي:** فعّل **Backups** في Enhance. الحاجات اللي محتاجة backup هي قاعدة البيانات، و`public_html/uploads/`، و`eagles-private/config.php`.
- **تحديث الكود بعدين:** ارفع الملفات اللي اتغيّرت بس. **ما تستبدلش** `eagles-private/` ولا `data/` ولا `uploads/`،
  وما ترفعش `install.php` تاني (ولو رفعته بالغلط، هيفضل مقفول وبيرجّع 403).
- **عمل ملف ZIP للرفع من git:**
  ```bash
  git archive --format=zip -o eagles-tourism-upload.zip HEAD -- . ':!README.md' ':!.gitignore' ':!DEPLOYMENT.md'
  ```
