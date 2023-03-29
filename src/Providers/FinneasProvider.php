<?php

namespace StoreNotifier\Providers;

use Illuminate\Support\Arr;
use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use Symfony\Component\DomCrawler\Crawler;

class FinneasProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'finneas';
    }

    public static function getTitle(): string
    {
        return 'Finneas';
    }

    public static function getUrl(): string
    {
        return 'https://www.finneasofficial.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(),
        ];
    }

    public function handle(): void
    {
        $contents = self::newHttpClient()
            ->get(self::getUrl() . '/store')
            ->getBody()
            ->getContents();

        $products = [];

        $crawler = new Crawler($contents);
        $crawler
            ->filter('.productgrid .grid-item')
            ->each(function (Crawler $crawler) use (&$products) {
                $link = $crawler->filter('figure a')->first();

                $detailPath = $link->attr('href');
                $detailUrl = self::getUrl() . $detailPath;

                $detailContent = self::newHttpClient()
                    ->get($detailUrl)
                    ->getBody()
                    ->getContents();

                $detailCrawler = new Crawler($detailContent);

                $price = (int) str_replace(['$', '.'], ['', ''], $detailCrawler->filter('section h2 .subheading')->first()->html());
                $title = $detailCrawler->filter('title')->first()->html();
                $img = $detailCrawler->filter('ul#product-images img')->first()->attr('src');
                $id = Arr::first(explode('-', str_replace('/products/', '', $detailPath)));

                $title = trim(
                    Arr::first(
                        explode('-', trim(str_replace(PHP_EOL, '', $title)))
                    )
                );

                $product = new ProductData([
                    'store_product_id' => $id,
                    'title' => $title,
                    'url' => $detailUrl,
                    'image_url' => $img,
                ]);

                $detailCrawler
                   ->filter('.variations #cart_variation_id option')
                   ->each(function (Crawler $optionsCrawler) use (&$product) {
                       if ('default' === $optionsCrawler->attr('id')) {
                           return;
                       }

                       $text = trim($optionsCrawler->html());

                       $ok = preg_match('/(?<price>[0-9.]+)\ \—\ (?<name>.*)/', $text, $matches);
                       if ( ! $ok) {
                           dd($text);
                       }

                       $available = ! str_contains($text, 'Unavailable');
                       $name = trim(str_replace('(Unavailable)', '', $matches['name']));

                       $price = (int) str_replace(['.', ','], ['', ''], $matches['price']);

                       $variant = new VariantData([
                           'store_variant_id' => $name,
                           'title' => $name,
                           'units_available' => null,
                           'available' => $available,
                           'price' => $price,
                           'currency' => 'USD',
                       ]);

                       $product->variants[] = $variant;
                   });

                if (empty($product->variants)) {
                    $description = $detailCrawler->filter('.content .row section .row div:nth-child(2)')->html();
                    $soldOut = str_contains($description, 'This item is SOLD OUT.');

                    $hasAddToCartButton = $detailCrawler->filter('.variations button')->count() > 0;

                    if ($hasAddToCartButton) {
                        $product->variants = [
                            new VariantData([
                                'store_variant_id' => 'Default',
                                'title' => 'Default',
                                'units_available' => null,
                                'available' => true,
                                'price' => $price,
                                'currency' => 'USD',
                            ]),
                        ];
                    }
                }

                $products[] = $product;
            });

        self::storeProducts($products);
    }
}
