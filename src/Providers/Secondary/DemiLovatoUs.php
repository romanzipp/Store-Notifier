<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class DemiLovatoUs extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'demi-lovato-us';
    }

    public static function getTitle(): string
    {
        return 'Demi Lovato (US)';
    }

    public static function getUrl(): string
    {
        return 'https://shop.demilovato.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
