<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class NickiMinajUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'nicky-minaj-uk';
    }

    public static function getTitle(): string
    {
        return 'Nicky Minaj (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.nickiminajofficial.com';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
