<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Providers\Data\Shopify\Collection;
use StoreNotifier\Providers\Data\Shopify\Product;

final class BillieEilishUsProvider extends AbstractProvider
{
    public function handle()
    {
        // https://store.billieeilish.com/products.json?page=2
        // https://store.billieeilish.com/collections.json
        // https://store.billieeilish.com/collections/apparel/products.json
        // https://store.billieeilish.com/collections/apparel/products/cut-out-red-tour-t-shirt.json
        $client = new Client([
            'base_uri' => 'https://store.billieeilish.com/',
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
            ],
        ]);

        $products = self::wrapArray(
            $client->get('products.json'),
            Product::class,
            fn (\stdClass $response) => $response->products
        );

        dd($products);

        $collections = self::wrapArray(
            $client->get('collections.json'),
            Collection::class,
            fn (\stdClass $response) => $response->collections
        );

        dd($collections);
    }
}
