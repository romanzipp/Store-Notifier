<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class ArianaGrandeUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'ariana-uk';
    }

    public static function getTitle(): string
    {
        return 'Ariana Grande (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.arianagrande.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
