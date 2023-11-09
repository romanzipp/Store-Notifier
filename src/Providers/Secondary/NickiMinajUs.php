<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class NickiMinajUs extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'nicky-minaj-us';
    }

    public static function getTitle(): string
    {
        return 'Nicky Minaj (US)';
    }

    public static function getUrl(): string
    {
        return 'https://shop.nickiminajofficial.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
