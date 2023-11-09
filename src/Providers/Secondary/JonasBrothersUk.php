<?php

namespace StoreNotifier\Providers\Secondary;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\AbstractShopifyProvider;

final class JonasBrothersUk extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'jonasbrothers-uk';
    }

    public static function getTitle(): string
    {
        return 'Jonas Brothers (UK)';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.jonasbrothers.com/';
    }

    public function getChannels(): array
    {
        return [
            new Telegram(Telegram::TYPE_SECONDARY),
        ];
    }
}
