<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Notifications\ErrorOccured;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use Symfony\Component\DomCrawler\Crawler;

class BillieEilishUkProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'billie-uk';
    }

    public static function getTitle(): string
    {
        return 'Billie UK';
    }

    public static function getUrl(): string
    {
        return 'https://shopuk.billieeilish.com';
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
        // Powered by https://www.digitalstores.co.uk/
        $mainUrls = [
            '*/Apparel/',
            '*/Accessories/',
            '*/Music/',
            '*/Kids/',
        ];

        $client = new Client([
            'base_uri' => self::getUrl(),
        ]);

        /** @var \StoreNotifier\Providers\Data\ModelData\ProductData[] $products */
        $products = [];

        foreach ($mainUrls as $url) {
            $this->logger->log("crawling product list on: {$url}", self::getId());
            $contents = $client->get($url)->getBody()->getContents();

            $crawler = new Crawler($contents);
            $productsElements = $crawler->filter('body dl.product');

            if (0 === $productsElements->count()) {
                $this->logger->log('empty product list');
                continue;
            }

            $productsElements->each(function (Crawler $crawler) use (&$products, $url) {
                try {
                    $title = $crawler->filter('dt.title a')->first()->text();
                    $productUrl = $crawler->filter('dt.title a')->first()->attr('href');
                    $image = $crawler->filter('img')->first()->attr('src');
                    $classes = array_filter(explode(' ', $crawler->attr('class')));

                    $id = null;
                    foreach ($classes as $class) {
                        if (str_contains($class, 'product')) {
                            continue;
                        }

                        $id = $class;
                        break;
                    }

                    if (null === $id) {
                        $this->logger->log("couldnt extract id from {$title}");
                    }

                    $products[] = new ProductData([
                        'store_product_id' => $id,
                        'title' => $title,
                        'url' => self::getUrl() . $productUrl,
                        'image_url' => $image,
                    ]);
                } catch (\Throwable $exception) {
                    $notification = new ErrorOccured($this, "Error crawling {$url}: {$exception->getMessage()}");
                    $notification->execute();

                    dump($exception);

                    return;
                }
            });
        }

        foreach ($products as $product) {
            $contents = $client->get($product->url)->getBody()->getContents();

            $crawler = new Crawler($contents);

            $price = $crawler->filter('#main .content .price')->first()->text();
            $price = str_replace('.', '', $price);
            $price = str_replace(',', '', $price);

            $currency = 'USD';
            foreach (['€' => 'EUR', '£' => 'GBP', '$' => 'USD'] as $symbol => $cu) {
                if (str_contains($price, $symbol)) {
                    $currency = $cu;
                    $price = str_replace($symbol, '', $price);
                }
            }

            $price = (int) $price;

            $crawler
                ->filter('body #main dl#variant dd')
                ->each(function (Crawler $crawler) use (&$product, $price, $currency) {
                    $title = trim($crawler->text());

                    $product->variants[] = new VariantData([
                        'store_variant_id' => $title,
                        'title' => $title,
                        'price' => $price,
                        'currency' => $currency,
                        'available' => 'unavailable' !== $crawler->attr('class'),
                    ]);
                });

            if (empty($product->variants)) {
                $variant = new VariantData([
                    'store_variant_id' => 'default',
                    'title' => 'Default',
                    'price' => $price,
                    'currency' => $currency,
                    'available' => false,
                ]);

                $canAdd = $crawler->filter('input[name="add"]')->count() > 0;
                $preOrder = $crawler->filter('input[name="preorder"]')->count() > 0;
                $soldOut = ($dispatch = $crawler->filter('li.dispatch'))->count() > 0 && str_contains($dispatch->text(), 'Sold Out');

                if ($canAdd) {
                    $variant->available = true;
                } elseif ($preOrder) {
                    $variant->title = 'Pre-Order';
                    $variant->available = true;
                } elseif ($soldOut) {
                    $variant->available = false;
                } else {
                    $this->logger->log("Unknown status for product {$product->title}");
                }

                $product->variants = [$variant];
            }

            $this->logger->logProduct($product);
        }

        $this->storeProducts($products);
    }
}
