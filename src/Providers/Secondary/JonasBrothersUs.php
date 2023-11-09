<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class JonasBrothersUs extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'jonasbrothers-us';
    }

    public static function getTitle(): string
    {
        return 'Jonas Brothers (US)';
    }

    public static function getUrl(): string
    {
        return 'https://shop.jonasbrothers.com/';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
