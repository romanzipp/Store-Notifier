<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use StoreNotifier\Channels\Pushover;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

class MpbProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'mpb';
    }

    public static function getTitle(): string
    {
        return 'MPB';
    }

    public static function getUrl(): string
    {
        return 'https://www.mpb.com';
    }

    public function handle(): void
    {
        $searchUrl = <<<EOF
        %s/search-service/product/query/
        ?filter_query[model_id]=%s
        &filter_query[model_market]=DE
        &filter_query[object_type]=product
        &filter_query[model_available]=true
        &filter_query[model_is_published_out]=true
        &field_list=model_name
        &field_list=model_description
        &field_list=product_price
        &field_list=model_url_segment
        &field_list=product_sku
        &field_list=product_condition
        &field_list=product_shutter_count
        &field_list=product_hour_count
        &field_list=product_battery_charge_count
        &field_list=product_id
        &field_list=product_images
        &field_list=model_id
        &field_list=product_price_reduction
        &field_list=product_price_original
        &field_list=product_price_modifiers
        &field_list=model_available_new
        &sort[product_last_online]=DESC
        &facet_minimum_count=1
        &facet_field=model_brand
        &facet_field=model_type
        &facet_field=product_condition_star_rating
        &facet_field=product_price
        &facet_field=%%2A
        &start=0
        &rows=100
        &minimum_match=100%%25
        EOF;

        $products = [
            new ProductData([
                'store_product_id' => '62958',
                'title' => 'Fujifilm X-Pro 1',
                'url' => 'https://www.mpb.com/de-de/produkt/fuji-x-pro-1?sort[productLastOnline]=DESC',
                'image_url' => 'https://www.mpb.com/cdn-cgi/image/width=286,quality=90,format=jpeg/media-service/31b08b9f-c244-4034-89c8-be1d417d7726',
            ]),
            new ProductData([
                'store_product_id' => '77405',
                'title' => 'Sigma 16-28mm F/2.8',
                'url' => 'https://www.mpb.com/de-de/produkt/sigma-16-28mm-f2f28-dg-dn-contemporary-sony-e-kompatibel?sort[productLastOnline]=DESC',
                'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/b2fbe187-70ad-4949-884c-d394615cb36c',
            ]),
            new ProductData([
                'store_product_id' => '66632',
                'title' => 'Sony FE 70-200mm f/2.8',
                'url' => 'https://www.mpb.com/de-de/produkt/sony-70-200mm-f-2-8-g-ssm-ii-sony-a-kompatibel?sort[productLastOnline]=DESC',
                'image_url' => 'https://www.mpb.com/cdn-cgi/image/width=286,quality=90,format=jpeg/media-service/2a3fa411-e929-45f7-b5ae-9419aaa1c834',
            ]),
        ];

        foreach ($products as $product) {
            $fullUrl = sprintf($searchUrl, self::getUrl(), $product->store_product_id);
            $fullUrl = str_replace(PHP_EOL, '', $fullUrl);

            $contents = self::newHttpClient([
                RequestOptions::HEADERS => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br, zstd',
                    'Accept-Language' => 'en-US,en;q=0.7,de;q=0.3',
                ],
            ])
                ->get($fullUrl, [
                    'headers' => [
                        'Content-Language' => 'de_DE',
                    ],
                    'cookies' => CookieJar::fromArray([
                        'mpb_user_location' => 'DE',
                    ], 'www.mpb.com'),
                ])
                ->getBody()
                ->getContents();

            $data = json_decode($contents);

            foreach ($data->results as $resultProduct) {
                $product->variants[] = new VariantData([
                    'store_variant_id' => array_values($resultProduct->product_id->values)[0],
                    'title' => ucfirst(strtolower(str_replace('_', ' ', array_values($resultProduct->product_condition->values)[0]))),
                    'price' => (int) array_values($resultProduct->product_price->values)[0],
                    'available' => true,
                    'currency' => 'EUR',
                ]);
            }

            $this->logger->logProduct($product);
        }

        $this->storeProducts($products);
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
            new Telegram(Telegram::TYPE_TILL),
        ];
    }
}
