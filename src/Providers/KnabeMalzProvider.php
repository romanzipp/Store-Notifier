<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Providers\Data\ModelData\ProductData;

class KnabeMalzProvider extends AbstractShopifyProvider
{
    public static function getId(): string
    {
        return 'knabe-malz';
    }

    public static function getTitle(): string
    {
        return 'Knabe Malz';
    }

    public static function getUrl(): string
    {
        return 'https://www.ready2drink.de';
    }

    public function shouldHandleProduct(ProductData $productData): bool
    {
        return '8450638381320' === $productData->store_product_id;
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }
}
