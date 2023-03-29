<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

class PhoebeBridgersUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'phoebe-us';
    }

    public static function getTitle(): string
    {
        return 'Phoebe US';
    }

    public static function getUrl(): string
    {
        return 'https://store.phoebefuckingbridgers.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(),
        ];
    }
}
