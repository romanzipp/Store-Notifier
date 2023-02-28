<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use StoreNotifier\Database\Database;
use StoreNotifier\Models\Event;
use StoreNotifier\Notifications\ErrorOccured;
use StoreNotifier\Providers\AbstractProvider;

new Database();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$filter = null;

if (in_array('--dry', $argv)) {
    AbstractProvider::$dryRun = true;
}

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--filter')) {
        $filter = trim(str_replace('--filter=', '', $arg));
    }
}

$providers = AbstractProvider::getAll();

if ($filter) {
    $providers = array_filter($providers, function (AbstractProvider $provider) use ($filter) {
        return str_contains($provider::getId(), $filter) || str_contains(get_class($provider), $filter);
    });
}

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
