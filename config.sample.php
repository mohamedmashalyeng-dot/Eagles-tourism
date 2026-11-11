<?php
/*
 * Normally install.php creates config.php for you.
 * To configure manually: copy this file to config.php, fill in your
 * Hostinger database details, then create an empty file data/installed.lock
 */
return [
    'db' => [
        'driver' => 'mysql',            // 'mysql' or 'sqlite'
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'u123456789_eagles',
        'user'   => 'u123456789_admin',
        'pass'   => 'YOUR_DB_PASSWORD',
        // for sqlite: 'path' => __DIR__ . '/data/eagles.sqlite',
    ],
    'site_url' => '',   // leave empty for auto-detection
    'debug'    => false,
];
