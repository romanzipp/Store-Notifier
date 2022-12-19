<?php

require __DIR__ . '/vendor/autoload.php';

use StoreNotifier\Database\Database;

$db = new Database();
$db->migrate();
