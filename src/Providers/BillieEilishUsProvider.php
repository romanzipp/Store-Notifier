<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Models\Product;

final class BillieEilishUsProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'billie-us';
    }

    public static function getTitle(): string
    {
        return 'Billie US';
    }

    public static function getUrl(): string
    {
        // https://store.billieeilish.com/products.json?page=2
        // https://store.billieeilish.com/collections.json
        // https://store.billieeilish.com/collections/apparel/products.json
        // https://store.billieeilish.com/collections/apparel/products/cut-out-red-tour-t-shirt.json
        return 'https://store.billieeilish.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_PRIMARY),
        ];
    }

    public static function productIgnoresNotifications(Product $product): bool
    {
        return in_array($product->title, [
            'HIT ME HARD AND SOFT SWEATSUIT',
        ]);
    }
}
