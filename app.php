<?php

require __DIR__ . '/vendor/autoload.php';

use StoreNotifier\Providers\AbstractProvider;

$providers = AbstractProvider::getAll();

foreach ($providers as $provider) {
    $provider->handle();
}
