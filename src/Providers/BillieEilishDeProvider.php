<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use Symfony\Component\DomCrawler\Crawler;

class BillieEilishDeProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'billie-de';
    }

    public static function getTitle(): string
    {
        return 'Bille (DE)';
    }

    public static function getUrl(): string
    {
        return 'https://www.billieeilishstore.de/';
    }

    public function handle(): void
    {
        $client = new Client();

        try {
            $contents = $client->get('https://www.bravado.de/p50-a157330/billie-eilish/index.html')->getBody()->getContents();
        } catch (ClientException $exception) {
            self::log('error requesting main content');
            self::log($exception->getMessage());

            return;
        }

        $products = [];

        $crawler = new Crawler($contents);
        $crawler
           ->filter('body #content div[role="list"] > div[role="listitem"]')
           ->each(function (Crawler $crawler) use (&$products, &$client) {
               $image = $crawler->filter('a.thumbnail')->first();

               $product = new ProductData([
                   'provider' => self::getId(),
                   'store_product_id' => $image->attr('data-id'),
                   'title' => $image->attr('data-name'),
                   'url' => $image->attr('href'),
               ]);

               $price = (int) str_replace(',', '', $image->attr('data-price'));

               try {
                   // https://www.bravado.de/pSFPAjaxProduct?id=0196177046412
                   $detailContents = $client->get($detailUrl = "https://www.bravado.de/pSFPAjaxProduct?id={$product->store_product_id}")->getBody()->getContents();
               } catch (ClientException $exception) {
                   self::log('error requesting detail content');
                   self::log("tried url: {$detailUrl}");
                   self::log("status: {$exception->getResponse()->getStatusCode()}");
                   self::log($exception->getMessage());

                   return;
               }

               $detailCrawler = new Crawler($detailContents);
               $detailCrawler
                   ->filter('form[data-available-variants] button[data-id]')
                   ->each(function (Crawler $detailCrawler) use ($price, &$product) {
                       $variant = new VariantData([
                           'store_variant_id' => $detailCrawler->attr('data-id'),
                           'title' => $detailCrawler->text(),
                           'units_available' => (int) ($unitsAvailable = $detailCrawler->attr('data-stock')),
                           'available' => $unitsAvailable > 0,
                           'price' => $price,
                           'currency' => 'EUR',
                       ]);

                       $product->variants[] = $variant;
                   });

               $products[] = $product;

               self::logProduct($product);
           });

        $this->storeProducts($products);
    }
}
