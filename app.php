<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use StoreNotifier\Database\Database;
use StoreNotifier\Notifications\ErrorOccured;
use StoreNotifier\Providers\AbstractProvider;

new Database();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$providers = AbstractProvider::getAll();

foreach ($providers as $provider) {
    try {
        $provider->handle();
    } catch (Throwable $exception) {
        $notification = new ErrorOccured($provider, $exception->getMessage());
        $notification->handle();

        dump($exception);
    }
}
