<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;

final class BringMeTheHorizonProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'bmth';
    }

    public static function getTitle(): string
    {
        return 'BMTH';
    }

    public static function getUrl(): string
    {
        return 'https://www.horizonsupply.co';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }
}
