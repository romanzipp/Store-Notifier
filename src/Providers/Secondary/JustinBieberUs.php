<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class JustinBieberUs extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'justin-bieber-us';
    }

    public static function getTitle(): string
    {
        return 'Justin Bieber (US)';
    }

    public static function getUrl(): string
    {
        return 'https://shop.justinbiebermusic.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
