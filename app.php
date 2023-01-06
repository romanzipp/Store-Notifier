<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use StoreNotifier\Database\Database;
use StoreNotifier\Log\Logger;
use StoreNotifier\Models\Event;
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
        $provider->logEvent(Event::TYPE_ERROR, $exception->getMessage());

        if ( ! $provider->hasRecenctError()) {
            $notification = new ErrorOccured($provider, $exception->getMessage());
            $notification->execute();
        }

        $provider->logger->log("ERROR in {$provider::getId()}: {$exception->getMessage()}");
    }
}
