<?php

namespace StoreNotifier\Providers;

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
}
