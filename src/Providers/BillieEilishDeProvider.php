<?php

namespace StoreNotifier\Providers;

use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
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

    private function retryRequest(\Closure $requestClosure, int $max = 5): Response
    {
        $i = 0;
        while (true) {
            try {
                return $requestClosure();
            } catch (ClientException $exception) {
                if (429 !== $exception->getResponse()->getStatusCode() || $i > $max) {
                    throw $exception;
                }

                ++$i;
                $wait = ($retryAfter = $exception->getResponse()->getHeaderLine('Retry-After'))
                    ? min((int) $retryAfter, 5)
                    : 5;

                $this->logger->log("encountered 429 Too Many Requests. Waiting {$wait} seconds... (try {$i}/{$max})");

                sleep($wait);
            }
        }
    }

    public function handle(): void
    {
        try {
            $contents = $this->retryRequest(fn () => self::newHttpClient()->get('https://www.bravado.de/p50-a157330/billie-eilish/index.html'))
                ->getBody()
                ->getContents();
        } catch (ClientException $exception) {
            $this->logger->log('error requesting main content');
            $this->logger->log($exception->getMessage());

            return;
        }

        $products = [];

        $crawler = new Crawler($contents);
        $crawler
           ->filter('body #content div[role="list"] > div[role="listitem"]')
           ->each(function (Crawler $crawler) use (&$products) {
               $link = $crawler->filter('a.thumbnail')->first();
               $image = $crawler->filter('img')->first();

               $product = new ProductData([
                   'store_product_id' => $link->attr('data-id'),
                   'title' => $link->attr('data-name'),
                   'url' => $link->attr('href'),
                   'image_url' => $image->attr('src') ?? null,
               ]);

               $price = (int) str_replace(',', '', $link->attr('data-price'));

               try {
                   // https://www.bravado.de/pSFPAjaxProduct?id=0196177046412
                   $detailUrl = "https://www.bravado.de/pSFPAjaxProduct?id={$product->store_product_id}";

                   $detailContents = self::retryRequest(fn () => self::newHttpClient()->get($detailUrl))
                       ->getBody()
                       ->getContents();
               } catch (ClientException $exception) {
                   $this->logger->log('error requesting detail content');
                   $this->logger->log("tried url: {$detailUrl}");
                   $this->logger->log("status: {$exception->getResponse()->getStatusCode()}");
                   $this->logger->log($exception->getMessage());

                   return;
               }

               $detailCrawler = new Crawler($detailContents);

               $publishedAt = $detailCrawler
                   ->filter('.date > span:not(.detail-label)')
                   ->text();

               if ($publishedAt) {
                   $product->published_at = (string) Carbon::createFromFormat('d.m.Y', $publishedAt);
               }

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

               if (empty($product->variants)) {
                   $product->variants = [new VariantData([
                       'store_variant_id' => "{$product->store_product_id}_default",
                       'title' => 'Default',
                       'price' => $price,
                       'currency' => 'EUR',
                       'available' => true,
                   ])];
               }

               $products[] = $product;

               $this->logger->logProduct($product);
           });

        $this->storeProducts($products);
    }
}
