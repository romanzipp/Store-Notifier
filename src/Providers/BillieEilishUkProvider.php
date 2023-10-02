<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

class BillieEilishUkProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'billie-uk';
    }

    public static function getTitle(): string
    {
        return 'Billie UK';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.billieeilish.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(),
        ];
    }
}
