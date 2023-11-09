<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class DemiLovatoUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'demi-lovato-uk';
    }

    public static function getTitle(): string
    {
        return 'Demi Lovato (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.demilovato.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
