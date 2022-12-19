<?php

require __DIR__ . '/vendor/autoload.php';

use StoreNotifier\Database\Database;
use StoreNotifier\Providers\AbstractProvider;

new Database();

$providers = AbstractProvider::getAll();

foreach ($providers as $provider) {
    $provider->handle();
}
