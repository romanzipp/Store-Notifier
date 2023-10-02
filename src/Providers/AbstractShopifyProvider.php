<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use StoreNotifier\Providers\Data\Shopify\ShopifyProduct;
use StoreNotifier\Providers\Data\Shopify\ShopifyVariant;

abstract class AbstractShopifyProvider extends AbstractProvider
{
    final public function handle(): void
    {
        $client = new Client([
            'base_uri' => $baseUri = static::getUrl() . '/',
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
            ],
        ]);

        /** @var \StoreNotifier\Providers\Data\Shopify\ShopifyProduct[] $shopifyProducts */
        $shopifyProducts = self::wrapArray(
            $client->get('products.json', [
                'query' => [
                    'limit' => 500,
                ],
            ]),
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

            $models[] = $product = new ProductData([
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

            $this->logger->logProduct($product);
        }

        $this->storeProducts($models);
    }
}
