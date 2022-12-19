<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Providers\Data\Shopify\ShopifyProduct;

final class BillieEilishUsProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'billie-us';
    }

    public function handle(): void
    {
        // https://store.billieeilish.com/products.json?page=2
        // https://store.billieeilish.com/collections.json
        // https://store.billieeilish.com/collections/apparel/products.json
        // https://store.billieeilish.com/collections/apparel/products/cut-out-red-tour-t-shirt.json
        $client = new Client([
            'base_uri' => $baseUri = 'https://store.billieeilish.com/',
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
            ],
        ]);

        /** @var \StoreNotifier\Providers\Data\Shopify\ShopifyProduct[] $shopifyProducts */
        $shopifyProducts = self::wrapArray(
            $client->get('products.json'),
            ShopifyProduct::class,
            fn (\stdClass $response) => $response->products
        );

        $models = [];

        foreach ($shopifyProducts as $shopifyProduct) {
            $models[] = new \StoreNotifier\Providers\Data\ModelData\ProductData([
                'store_product_id' => (string) $shopifyProduct->id,
                'title' => $shopifyProduct->title,
                'url' => "{$baseUri}products/{$shopifyProduct->handle}",
            ]);
        }

        $this->storeProducts($models);
    }
}
