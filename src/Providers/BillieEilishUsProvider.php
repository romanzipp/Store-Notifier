<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use StoreNotifier\Providers\Data\Shopify\ShopifyProduct;
use StoreNotifier\Providers\Data\Shopify\ShopifyVariant;

final class BillieEilishUsProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'billie-us';
    }

    public static function getTitle(): string
    {
        return 'Billie Eilish (US)';
    }

    public static function getUrl(): string
    {
        return 'https://store.billieeilish.com';
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
            $imageUrl = null;

            foreach ($shopifyProduct->images as $image) {
                $imageUrl = $image->src;
                break;
            }

            $models[] = new ProductData([
                'store_product_id' => (string) $shopifyProduct->id,
                'title' => $shopifyProduct->title,
                'url' => "{$baseUri}products/{$shopifyProduct->handle}",
                'published_at' => $shopifyProduct->published_at,
                'image_url' => $imageUrl,
                'variants' => array_map(fn (ShopifyVariant $shopifyVariant) => new VariantData([
                    'store_variant_id' => (string) $shopifyVariant->id,
                    'title' => $shopifyVariant->title,
                    'price' => (int) str_replace('.', '', $shopifyVariant->price),
                    'available' => $shopifyVariant->available,
                ]), $shopifyProduct->variants),
            ]);
        }

        $this->storeProducts($models);
    }
}
