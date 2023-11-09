<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class SelenaGomezUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'selena-gomez-uk';
    }

    public static function getTitle(): string
    {
        return 'Selena Gomez (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://storeuk.selenagomez.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
