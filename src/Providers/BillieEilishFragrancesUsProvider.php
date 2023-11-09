<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

final class BillieEilishFragrancesUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'billie-fragrances';
    }

    public static function getTitle(): string
    {
        return 'Billie Fragrances';
    }

    public static function getUrl(): string
    {
        return 'https://billieeilishfragrances.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_PRIMARY),
        ];
    }
}
