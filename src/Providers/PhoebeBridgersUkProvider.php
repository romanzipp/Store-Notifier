<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;

class PhoebeBridgersUkProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'phoebe-uk';
    }

    public static function getTitle(): string
    {
        return 'Phoebe UK';
    }

    public static function getUrl(): string
    {
        return 'https://phoebe-bridgers-uk.myshopify.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }
}
