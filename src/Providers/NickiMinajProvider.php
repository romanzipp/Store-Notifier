<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;

final class NickiMinajProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'nicky-minaj';
    }

    public static function getTitle(): string
    {
        return 'Nicky Minaj';
    }

    public static function getUrl(): string
    {
        return 'https://shop.nickiminajofficial.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
