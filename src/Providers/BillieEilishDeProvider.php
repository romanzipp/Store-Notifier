<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

class BillieEilishDeProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'billie-de';
    }

    public static function getTitle(): string
    {
        return 'Billie DE';
    }

    public static function getUrl(): string
    {
        return 'https://www.billieeilishstore.de';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_PRIMARY),
        ];
    }
}
