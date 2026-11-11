<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
start_session();
$_SESSION = [];
session_regenerate_id(true);
session_destroy();
redirect('admin/login.php');
