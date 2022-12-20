<?php

namespace StoreNotifier\Providers;

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
        // https://www.bravado.de/p50-a157330/billie-eilish/index.html
        $contents = file_get_contents('https://www.bravado.de/p50-a157330/billie-eilish/index.html');

        $products = [];

        $crawler = new Crawler($contents);
        $crawler
           ->filter('body #content div[role="list"] > div[role="listitem"]')
           // ->siblings()
           ->each(function (Crawler $crawler) use (&$products) {
               $image = $crawler->filter('a.thumbnail')->first();

               // dd($image);

               $product = new ProductData([
                   'store_product_id' => $image->attr('data-id'),
                   'title' => $image->attr('data-name'),
                   'url' => $image->attr('href'),
               ]);

               $price = (int) str_replace(',', '', $image->attr('data-price'));

               // https://www.bravado.de/pSFPAjaxProduct?id=0196177046412
               $detailContents = @file_get_contents("https://www.bravado.de/pSFPAjaxProduct?id={$product->store_product_id}");

               $detailCrawler = new Crawler($detailContents);
               $detailCrawler
                   ->filter('form[data-available-variants] button[data-id]')
                   // ->siblings()
                   ->each(function (Crawler $detailCrawler) use ($price, &$product) {
                       if ( ! $detailCrawler->attr('data-id')) {
                           dd(
                               $detailCrawler->outerHtml()
                           );
                           dd(iterator_to_array($detailCrawler->getNode(0)->attributes));
                       }

                       $variant = new VariantData([
                           'store_variant_id' => $detailCrawler->attr('data-id'),
                           'title' => $detailCrawler->text(),
                           'units_available' => (int) ($unitsAvailable = $detailCrawler->attr('data-stock')),
                           'available' => $unitsAvailable > 0,
                           'price' => $price,
                       ]);

                       $product->variants[] = $variant;
                   });

               $products[] = $product;
           });

        $this->storeProducts($products);
    }
}
