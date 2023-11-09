<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class JustinBieberUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'justin-bieber-uk';
    }

    public static function getTitle(): string
    {
        return 'Justin Bieber (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.justinbiebermusic.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
