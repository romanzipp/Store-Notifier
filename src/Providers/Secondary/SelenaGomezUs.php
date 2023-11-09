<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class SelenaGomezUs extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'selena-gomez-us';
    }

    public static function getTitle(): string
    {
        return 'Selena Gomez (US)';
    }

    public static function getUrl(): string
    {
        return 'https://store.selenagomez.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
