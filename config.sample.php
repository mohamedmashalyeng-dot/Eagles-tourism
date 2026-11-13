<?php
/*
 * Normally install.php creates the config file for you.
 *
 * Where the config file lives (first match wins):
 *   1. ../eagles-private/config.php  — outside public_html (recommended, used by the installer when possible)
 *   2. ./config.php                  — inside the site folder (fallback, web access denied by .htaccess)
 *
 * To configure manually: copy this file to one of those locations, fill in the
 * database details from Enhance (Databases tab), then create data/installed.lock
 */
return [
    'db' => [
        'driver' => 'mysql',            // 'mysql' or 'sqlite'
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'eagles_db',
        'user'   => 'eagles_user',
        'pass'   => 'YOUR_DB_PASSWORD',
        // for sqlite: 'path' => 'data/eagles.sqlite',   (relative to the site folder)
    ],
    'site_url' => '',   // leave empty for auto-detection
    'debug'    => false,
];
