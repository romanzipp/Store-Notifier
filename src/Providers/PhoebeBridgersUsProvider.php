<?php

namespace StoreNotifier\Providers;

class PhoebeBridgersUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'phoebe-us';
    }

    public static function getTitle(): string
    {
        return 'Phoebe (US)';
    }

    public static function getUrl(): string
    {
        return 'https://store.phoebefuckingbridgers.com';
    }
}
