<?php
/** Run with PHP + mbstring + pdo_sqlite: php tests/owner-content.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('INSTALLER', true);
require dirname(__DIR__) . '/includes/bootstrap.php';
require ROOT . '/includes/schema.php';

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function test_database(): PDO
{
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    install_schema($pdo, 'sqlite');
    return $pdo;
}

$pdo = test_database();
seed_sample_content($pdo);
seed_spa_services($pdo);
// Reproduce a v4 installation with published examples and no price-confirmation columns.
$pdo->exec('ALTER TABLE trips DROP COLUMN price_confirmed');
$pdo->exec('ALTER TABLE trips DROP COLUMN price_currency');
$pdo->exec('UPDATE trips SET is_active = 1');
$pdo->exec('UPDATE places SET is_active = 1');
$pdo->exec("UPDATE trips SET description = 'Owner edited description', price_adult = 75 WHERE id = 1");
$pdo->exec('UPDATE trips SET price_adult = 80 WHERE id = 2');
$pdo->exec("UPDATE trips SET duration = 'Owner duration' WHERE id = 4");
$pdo->exec("UPDATE trip_translations SET short_description = 'Owner English copy' WHERE trip_id = 5 AND lang = 'en'");
$pdo->exec("INSERT INTO trip_images (trip_id, path, sort_order) VALUES (6, 'uploads/trips/real.jpg', 1)");
$pdo->exec("UPDATE places SET image = 'uploads/places/real.jpg' WHERE id = 1");
$setting = $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?,?)');
$setting->execute(['stat_clients', '5000+']);
$setting->execute(['stat_rating', '4.8']); // business override
$setting->execute(['address', 'شارع الشيراتون، السقالة، الغردقة، البحر الأحمر، مصر']);
upgrade_owner_content($pdo, 'sqlite');

$massage = $pdo->query("SELECT * FROM trips WHERE title = 'مساج بزيت طبيعي'")->fetch();
$beauty = $pdo->query("SELECT * FROM trips WHERE category = 'beauty'")->fetch();
check($massage !== false && $beauty !== false, 'Owner services must be created.');
check((float)$massage['price_adult'] === 15.0 && $massage['duration'] === '45 دقيقة', 'Massage must cost 15 for 45 minutes.');
check($massage['price_currency'] === 'EUR' && has_public_price($massage), 'Massage must have a confirmed EUR price.');
check(public_price($massage) === '15 €', 'Massage currency must not use the global EGP setting.');
check(!has_public_price($beauty), 'Beauty must have no invented price.');
check(!has_public_price(['price_adult' => 1500]), 'Legacy prices must stay private.');
check(!has_public_price(['price_adult' => 0, 'price_confirmed' => 1]), 'Zero must not be advertised as a free service.');
check(!has_public_price(['price_adult' => -1, 'price_confirmed' => 1]), 'Negative prices must stay private.');
check((int)$pdo->query('SELECT is_active FROM trips WHERE id = 1')->fetchColumn() === 1, 'Edited content must survive.');
check((int)$pdo->query('SELECT is_active FROM trips WHERE id = 2')->fetchColumn() === 1, 'Edited prices must survive.');
check((int)$pdo->query('SELECT is_active FROM trips WHERE id = 3')->fetchColumn() === 0, 'Untouched sample trips must be hidden.');
foreach ([4, 5, 6] as $editedId) check((int)$pdo->query('SELECT is_active FROM trips WHERE id = ' . $editedId)->fetchColumn() === 1, 'Preserve edited durations, translations and galleries.');
check((int)$pdo->query('SELECT COUNT(*) FROM places WHERE is_active = 1')->fetchColumn() === 1, 'Keep real uploaded photos and hide stock examples.');
check($pdo->query("SELECT svalue FROM settings WHERE skey = 'stat_clients'")->fetchColumn() === '', 'Sample statistics must be cleared.');
check($pdo->query("SELECT svalue FROM settings WHERE skey = 'stat_rating'")->fetchColumn() === '4.8', 'Edited statistics must survive.');
check($pdo->query("SELECT svalue FROM settings WHERE skey = 'address'")->fetchColumn() === DEFAULT_SETTINGS['address'], 'Replace the sample address.');
check((int)$pdo->query('SELECT COUNT(*) FROM trip_translations WHERE trip_id IN (' . (int)$massage['id'] . ',' . (int)$beauty['id'] . ')')->fetchColumn() === 8, 'Both services need all four translations.');

$card = trip_card($massage);
check(str_contains($card, '15 €') && !str_contains($card, '<del>'), 'Card must show the confirmed price without a fake discount.');
check(!str_contains(trip_card($beauty), '0 €'), 'Unknown prices must not render as zero.');
$locations = locations_section();
check(substr_count($locations, '<iframe ') === 3, 'Render three maps.');
check(substr_count($locations, 'https://www.google.com/maps/search/?api=1') === 3, 'Every address needs a map link.');
check(count(business_locations()) === 3, 'Exactly three supplied addresses are supported.');
foreach (LANGS as $code => $_) {
    if ($code === DEFAULT_LANG) continue;
    $dictionary = require ROOT . '/includes/lang/' . $code . '.php';
    check($dictionary['@settings']['testimonials'] === '', 'No sample reviews in any language.');
    check(!str_contains($dictionary['@settings']['address'], '??'), 'Translations must retain Unicode.');
    check(isset($dictionary['بيوتي'], $dictionary['سعر الفرد'], $dictionary['عناويننا في الأحياء']), 'Translate new interface labels.');
}

$count = (int)$pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn();
$pdo->prepare('UPDATE trips SET price_adult = 18, is_active = 0 WHERE id = ?')->execute([$massage['id']]);
upgrade_owner_content($pdo, 'sqlite');
check((int)$pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn() === $count, 'Repeated upgrades must not duplicate services.');
check((float)$pdo->query('SELECT price_adult FROM trips WHERE id = ' . (int)$massage['id'])->fetchColumn() === 18.0, 'Do not overwrite owner changes on retry.');
check((int)$pdo->query('SELECT is_active FROM trips WHERE id = ' . (int)$massage['id'])->fetchColumn() === 0, 'Do not republish a service on retry.');

$fresh = test_database();
upgrade_owner_content($fresh, 'sqlite');
check((int)$fresh->query('SELECT COUNT(*) FROM trips')->fetchColumn() === 2, 'Fresh installs need only the owner services.');
echo "PASS: v4 upgrade, fresh install, price visibility, translations, maps, and owner changes.\n";
