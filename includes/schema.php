<?php
/** Database schema + sample content used by install.php */

function install_schema(PDO $pdo, string $driver): void
{
    $id     = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
    $fk     = $driver === 'sqlite' ? 'INTEGER' : 'INT UNSIGNED';
    $engine = $driver === 'sqlite' ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    $sql = [
        "CREATE TABLE IF NOT EXISTS users (
            id $id,
            username VARCHAR(60) NOT NULL UNIQUE,
            full_name VARCHAR(120) NOT NULL DEFAULT '',
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS settings (
            skey VARCHAR(60) NOT NULL PRIMARY KEY,
            svalue TEXT
        )$engine",
        "CREATE TABLE IF NOT EXISTS trips (
            id $id,
            title VARCHAR(200) NOT NULL,
            category VARCHAR(30) NOT NULL DEFAULT 'sea',
            short_description VARCHAR(500) NOT NULL DEFAULT '',
            description TEXT,
            duration VARCHAR(100) NOT NULL DEFAULT '',
            location VARCHAR(150) NOT NULL DEFAULT '',
            pickup_time VARCHAR(100) NOT NULL DEFAULT '',
            group_size VARCHAR(100) NOT NULL DEFAULT '',
            price_adult DECIMAL(10,2) NOT NULL DEFAULT 0,
            price_child DECIMAL(10,2) NOT NULL DEFAULT 0,
            old_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            includes TEXT,
            excludes TEXT,
            itinerary TEXT,
            cover_image VARCHAR(500) NOT NULL DEFAULT '',
            is_featured TINYINT NOT NULL DEFAULT 0,
            is_active TINYINT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS trip_images (
            id $id,
            trip_id $fk NOT NULL,
            path VARCHAR(500) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
        )$engine",
        "CREATE TABLE IF NOT EXISTS places (
            id $id,
            name VARCHAR(150) NOT NULL,
            description VARCHAR(600) NOT NULL DEFAULT '',
            image VARCHAR(500) NOT NULL DEFAULT '',
            is_active TINYINT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS bookings (
            id $id,
            trip_id $fk NULL,
            trip_title VARCHAR(200) NOT NULL DEFAULT '',
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            email VARCHAR(150) NOT NULL DEFAULT '',
            travel_date DATE NULL,
            adults INT NOT NULL DEFAULT 1,
            children INT NOT NULL DEFAULT 0,
            hotel VARCHAR(150) NOT NULL DEFAULT '',
            notes TEXT,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            created_at DATETIME NOT NULL
        )$engine",
    ];
    foreach ($sql as $q) {
        $pdo->exec($q);
    }
}

function u(string $id, int $w = 1600): string
{
    return "https://images.unsplash.com/photo-$id?auto=format&fit=crop&w=$w&q=80";
}

function seed_sample_content(PDO $pdo): void
{
    $now = date('Y-m-d H:i:s');

    $trips = [
        [
            'title' => 'رحلة جزيرة أورانج باي',
            'category' => 'sea',
            'short' => 'يوم كامل على أجمل شواطئ جزيرة الجفتون: رمال بيضاء، مياه فيروزية، وسنوركل بين الشعاب المرجانية.',
            'desc' => "انطلق معنا على متن يخت فاخر من مارينا الغردقة إلى جزيرة أورانج باي، واحدة من أشهر جزر البحر الأحمر بشاطئها الأبيض ومياهها الصافية.\n\nخلال الرحلة نتوقف عند موقعين للسنوركل لمشاهدة الشعاب المرجانية والأسماك الملونة بصحبة مرشدين محترفين، ثم نقضي وقتاً حراً على الشاطئ للاسترخاء والسباحة والتصوير، مع وجبة غداء طازجة على متن اليخت.",
            'duration' => '8 ساعات (يوم كامل)', 'location' => 'جزيرة الجفتون', 'pickup' => '8:00 صباحاً من الفندق', 'group' => 'حتى 40 فرد',
            'adult' => 1500, 'child' => 750, 'old' => 1800,
            'inc' => "انتقالات من وإلى الفندق\nرحلة اليخت ذهاباً وعودة\nمعدات السنوركل\nوجبة غداء ومشروبات\nمرشد سياحي\nرسوم دخول الجزيرة",
            'exc' => "التصوير تحت الماء\nالأنشطة المائية الإضافية (باراسيلينج - بنانا)",
            'itin' => "08:00 - الاستلام من الفندق\n09:00 - الإبحار من مارينا الغردقة\n10:30 - أول توقف للسنوركل\n12:00 - الوصول إلى جزيرة أورانج باي\n14:00 - الغداء على متن اليخت\n15:00 - توقف ثانٍ للسنوركل\n17:00 - العودة إلى المارينا",
            'cover' => u('1507525428034-b723cf961d3e'), 'featured' => 1,
            'gallery' => [u('1544551763-46a013bb70d5'), u('1546026423-cc4642628d2b'), u('1519046904884-53103b34b206')],
        ],
        [
            'title' => 'سفاري سوبر بالموتوسيكلات الرباعية',
            'category' => 'safari',
            'short' => 'مغامرة في قلب الصحراء الشرقية: كواد بايك، ركوب الجمال، زيارة قرية بدوية، وعشاء مع عرض فلكلوري.',
            'desc' => "تجربة الصحراء الحقيقية! نبدأ بقيادة الموتوسيكلات الرباعية (كواد) وسط الكثبان والجبال، ثم نصل إلى قرية بدوية أصيلة لنتعرف على حياة البدو وعاداتهم ونشرب الشاي البدوي.\n\nبعدها ركوب الجمال ومشاهدة غروب الشمس فوق الجبال، ونختتم بعشاء مشويات وعرض تنورة ورقص شرقي تحت النجوم.",
            'duration' => '5 ساعات', 'location' => 'الصحراء الشرقية', 'pickup' => '2:00 ظهراً من الفندق', 'group' => 'مجموعات صغيرة',
            'adult' => 1200, 'child' => 600, 'old' => 0,
            'inc' => "انتقالات بسيارة مكيفة\nكواد بايك لمدة ساعة\nركوب الجمال\nزيارة القرية البدوية\nعشاء بوفيه مشويات\nعرض التنورة والفلكلور",
            'exc' => "مشروبات غازية إضافية\nالبقشيش",
            'itin' => "14:00 - الاستلام من الفندق\n15:00 - تعليمات الأمان وبداية رحلة الكواد\n16:30 - الوصول إلى القرية البدوية\n17:15 - ركوب الجمال ومشاهدة الغروب\n18:30 - العشاء والعرض الفلكلوري\n20:00 - العودة إلى الفندق",
            'cover' => u('1509316785289-025f5b846b35'), 'featured' => 1,
            'gallery' => [u('1473580044384-7ba9967e16a0'), u('1547234935-80c7145ec969')],
        ],
        [
            'title' => 'رحلة بيت الدلافين',
            'category' => 'sea',
            'short' => 'أبحر إلى شعاب "بيت الدلافين" واستمتع بفرصة السباحة بالقرب من الدلافين في بيئتها الطبيعية.',
            'desc' => "رحلة بحرية ممتعة لكل أفراد العائلة إلى منطقة شعاب أبو رماده الشهيرة بتواجد الدلافين. نبحر بهدوء ونراقب الدلافين، ومع الظروف المناسبة يمكنك السباحة بالقرب منها.\n\nتشمل الرحلة توقفين للسنوركل على شعاب مرجانية مذهلة ووجبة غداء على اليخت.",
            'duration' => '7 ساعات', 'location' => 'شعاب أبو رماده', 'pickup' => '8:30 صباحاً', 'group' => 'حتى 35 فرد',
            'adult' => 1700, 'child' => 850, 'old' => 0,
            'inc' => "انتقالات من وإلى الفندق\nرحلة اليخت\nمعدات السنوركل\nغداء ومشروبات\nمرشد بحري",
            'exc' => "رسوم الميناء البيئية (إن وجدت)",
            'itin' => "08:30 - الاستلام من الفندق\n09:30 - الإبحار\n11:00 - البحث عن الدلافين\n12:30 - سنوركل على الشعاب\n13:30 - الغداء\n15:30 - العودة",
            'cover' => u('1570481662006-a3a1374699e8'), 'featured' => 1,
            'gallery' => [u('1583212292454-1fe6229603b7'), u('1559827260-dc66d52bef19')],
        ],
        [
            'title' => 'غوص للمبتدئين (تجربة الغوص الأولى)',
            'category' => 'diving',
            'short' => 'لم تغص من قبل؟ جرّب الغوص لأول مرة مع مدربين معتمدين في واحدة من أجمل مناطق الغوص في العالم.',
            'desc' => "برنامج مثالي لمن يريد تجربة الغوص لأول مرة دون أي خبرة سابقة. يبدأ بشرح نظري مبسط وتدريب في المياه الضحلة، ثم غطستان مع مدرب خاص على عمق آمن لمشاهدة الشعاب والكائنات البحرية.",
            'duration' => '7 ساعات', 'location' => 'جزيرة الجفتون', 'pickup' => '8:00 صباحاً', 'group' => 'مدرب لكل 2 غواصين',
            'adult' => 2500, 'child' => 2000, 'old' => 2900,
            'inc' => "انتقالات من وإلى الفندق\nمعدات الغوص كاملة\nمدرب غوص معتمد\nغطستان\nغداء ومشروبات",
            'exc' => "الصور والفيديو تحت الماء\nشهادة الغوص الدولية",
            'itin' => "08:00 - الاستلام من الفندق\n09:00 - الشرح النظري وتجهيز المعدات\n10:30 - الغطسة الأولى\n12:30 - الغداء والراحة\n14:00 - الغطسة الثانية\n16:00 - العودة",
            'cover' => u('1544551763-77ef2d0cfc6c'), 'featured' => 1,
            'gallery' => [u('1546026423-cc4642628d2b'), u('1544551763-46a013bb70d5')],
        ],
        [
            'title' => 'رحلة الأقصر ليوم واحد',
            'category' => 'day',
            'short' => 'زيارة متحف العالم المفتوح: معبد الكرنك، وادي الملوك، معبد حتشبسوت، وتمثالي ممنون.',
            'desc' => "رحلة تاريخية ليوم كامل إلى مدينة الأقصر مع مرشد متخصص في المصريات. نزور معابد الكرنك على البر الشرقي ثم نعبر إلى البر الغربي لزيارة وادي الملوك ومعبد الملكة حتشبسوت وتمثالي ممنون، مع غداء في مطعم على النيل.",
            'duration' => '16 ساعة', 'location' => 'الأقصر', 'pickup' => '4:30 صباحاً', 'group' => 'حتى 20 فرد',
            'adult' => 4500, 'child' => 2500, 'old' => 0,
            'inc' => "انتقالات بأتوبيس سياحي مكيف\nمرشد متخصص في المصريات\nرسوم دخول المزارات\nغداء في مطعم\nمياه معدنية",
            'exc' => "تذاكر المقابر الخاصة (توت عنخ آمون)\nرحلة البالون\nالبقشيش",
            'itin' => "04:30 - التحرك من الغردقة\n09:00 - معبد الكرنك\n11:30 - العبور للبر الغربي\n12:00 - وادي الملوك\n14:00 - معبد حتشبسوت وتمثالي ممنون\n15:30 - الغداء\n16:30 - العودة إلى الغردقة",
            'cover' => u('1568322445389-f64ac2515020'), 'featured' => 0,
            'gallery' => [u('1503177119275-0aa32b3a9368')],
        ],
        [
            'title' => 'الغواصة السياحية (سيمي سب)',
            'category' => 'family',
            'short' => 'شاهد عالم البحر الأحمر عبر نوافذ زجاجية دون أن تبتل — مثالية للأطفال وكبار السن.',
            'desc' => "رحلة عائلية رائعة على متن قارب زجاجي نصف غاطس، تتيح لك مشاهدة الشعاب المرجانية والأسماك الاستوائية عبر نوافذ بانورامية، مع توقف اختياري للسنوركل لمن يرغب.",
            'duration' => '3 ساعات', 'location' => 'الغردقة', 'pickup' => 'مواعيد متعددة يومياً', 'group' => 'مناسبة لكل الأعمار',
            'adult' => 1100, 'child' => 550, 'old' => 0,
            'inc' => "انتقالات من وإلى الفندق\nالرحلة على الغواصة\nمعدات السنوركل\nمشروب",
            'exc' => "الوجبات",
            'itin' => "الاستلام من الفندق\nالإبحار إلى منطقة الشعاب\nمشاهدة الحياة البحرية (45 دقيقة)\nتوقف للسنوركل\nالعودة",
            'cover' => u('1559128010-7c1ad6e1b6a5'), 'featured' => 0,
            'gallery' => [],
        ],
        [
            'title' => 'جولة الغردقة ليلاً',
            'category' => 'city',
            'short' => 'اكتشف روح الغردقة: المارينا، مسجد الميناء، الكاتدرائية القبطية، وسوق الدهار التقليدي.',
            'desc' => "جولة مسائية ممتعة في أهم معالم الغردقة: نبدأ بمسجد الميناء الكبير، ثم الكاتدرائية القبطية الشهيرة، مروراً بسوق الدهار التقليدي للتسوق والتوابل، ونختتم بالتمشية في مارينا الغردقة بين المطاعم والكافيهات.",
            'duration' => '4 ساعات', 'location' => 'وسط الغردقة', 'pickup' => '5:00 مساءً', 'group' => 'مجموعات صغيرة',
            'adult' => 700, 'child' => 350, 'old' => 0,
            'inc' => "انتقالات بسيارة مكيفة\nمرشد سياحي\nمشروب في المارينا",
            'exc' => "المشتريات الشخصية\nالعشاء",
            'itin' => "17:00 - الاستلام من الفندق\n17:30 - مسجد الميناء الكبير\n18:15 - الكاتدرائية القبطية\n19:00 - سوق الدهار\n20:00 - مارينا الغردقة\n21:00 - العودة",
            'cover' => u('1539650116574-8efeb43e2750'), 'featured' => 0,
            'gallery' => [],
        ],
        [
            'title' => 'جزيرة الجنة (باراديس)',
            'category' => 'sea',
            'short' => 'رحلة استرخاء إلى جزيرة باراديس: شاطئ هادئ، مياه كريستالية، وسنوركل ممتع.',
            'desc' => "استمتع بيوم هادئ على جزيرة الجنة، حيث الشواطئ الرملية والمياه الشفافة. تشمل الرحلة توقف للسنوركل ووقت حر على الجزيرة ووجبة غداء على اليخت.",
            'duration' => '7 ساعات', 'location' => 'جزر الغردقة', 'pickup' => '8:30 صباحاً', 'group' => 'حتى 40 فرد',
            'adult' => 1600, 'child' => 800, 'old' => 0,
            'inc' => "انتقالات من وإلى الفندق\nرحلة اليخت\nمعدات السنوركل\nغداء ومشروبات\nرسوم الجزيرة",
            'exc' => "الأنشطة المائية الإضافية",
            'itin' => "08:30 - الاستلام من الفندق\n09:30 - الإبحار\n11:00 - سنوركل\n12:00 - الجزيرة والوقت الحر\n14:00 - الغداء\n16:00 - العودة",
            'cover' => u('1540202404-a2f29016b523'), 'featured' => 0,
            'gallery' => [],
        ],
    ];

    $ins = $pdo->prepare('INSERT INTO trips (title, category, short_description, description, duration, location, pickup_time, group_size, price_adult, price_child, old_price, includes, excludes, itinerary, cover_image, is_featured, is_active, sort_order, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)');
    $img = $pdo->prepare('INSERT INTO trip_images (trip_id, path, sort_order) VALUES (?,?,?)');
    foreach ($trips as $i => $t) {
        $ins->execute([$t['title'], $t['category'], $t['short'], $t['desc'], $t['duration'], $t['location'], $t['pickup'], $t['group'],
            $t['adult'], $t['child'], $t['old'], $t['inc'], $t['exc'], $t['itin'], $t['cover'], $t['featured'], $i + 1, $now, $now]);
        $tripId = (int)$pdo->lastInsertId();
        foreach ($t['gallery'] as $j => $g) {
            $img->execute([$tripId, $g, $j]);
        }
    }

    $places = [
        ['جزيرة الجفتون', 'محمية طبيعية تضم بعضاً من أجمل الشعاب المرجانية والشواطئ البيضاء في البحر الأحمر.', u('1519046904884-53103b34b206', 1200)],
        ['أورانج باي', 'شاطئ ساحر بمياه فيروزية ورمال ناعمة، من أشهر وجهات التصوير في الغردقة.', u('1507525428034-b723cf961d3e', 1200)],
        ['مارينا الغردقة', 'واجهة بحرية حديثة تضم اليخوت والمطاعم والكافيهات، وأجمل مكان للتمشية مساءً.', u('1567899378494-47b22a2ae96a', 1200)],
        ['الشعاب المرجانية', 'عالم ملون تحت الماء يجعل الغردقة من أفضل مدن الغوص والسنوركل في العالم.', u('1546026423-cc4642628d2b', 1200)],
        ['الصحراء الشرقية', 'جبال وكثبان ذهبية خلف المدينة، مثالية لرحلات السفاري ومشاهدة الغروب.', u('1509316785289-025f5b846b35', 1200)],
        ['مسجد الميناء الكبير', 'تحفة معمارية على شاطئ البحر في منطقة الدهار، من أبرز معالم المدينة.', u('1539650116574-8efeb43e2750', 1200)],
        ['الجونة', 'مدينة المنتجعات والقنوات المائية شمال الغردقة، بطابعها المعماري المميز.', u('1548574505-5e239809ee19', 1200)],
        ['شاطئ البحر الأحمر', 'شمس مشرقة طوال العام وشواطئ ممتدة تجعل الغردقة وجهة مثالية لكل الفصول.', u('1559128010-7c1ad6e1b6a5', 1200)],
    ];
    $p = $pdo->prepare('INSERT INTO places (name, description, image, is_active, sort_order, created_at) VALUES (?,?,?,1,?,?)');
    foreach ($places as $i => [$name, $desc, $image]) {
        $p->execute([$name, $desc, $image, $i + 1, $now]);
    }
}
