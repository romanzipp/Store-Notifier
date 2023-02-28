<?php

namespace StoreNotifier\Providers;

class GirlInRedUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'girl-in-red-us';
    }

    public static function getTitle(): string
    {
        return 'girl in red US';
    }

    public static function getUrl(): string
    {
        return 'https://us.shopgirlinred.com';
    }
}
