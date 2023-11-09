<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class ArianaGrandeUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'ariana-us';
    }

    public static function getTitle(): string
    {
        return 'Ariana Grande (US)';
    }

    public static function getUrl(): string
    {
        return 'https://shop.arianagrande.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
