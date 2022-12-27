<?php

namespace StoreNotifier\Providers;

final class BillieEilishUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'billie-us';
    }

    public static function getTitle(): string
    {
        return 'Billie (US)';
    }

    public static function getUrl(): string
    {
        // https://store.billieeilish.com/products.json?page=2
        // https://store.billieeilish.com/collections.json
        // https://store.billieeilish.com/collections/apparel/products.json
        // https://store.billieeilish.com/collections/apparel/products/cut-out-red-tour-t-shirt.json
        return 'https://store.billieeilish.com';
    }
}
