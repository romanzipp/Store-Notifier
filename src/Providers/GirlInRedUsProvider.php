<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

class GirlInRedUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'girl-in-red-us';
    }

    public static function getTitle(): string
    {
        return 'girl in red US';
    }

    public static function getUrl(): string
    {
        return 'https://shopgirlinred.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_PRIMARY),
        ];
    }
}
