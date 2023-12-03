<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Cookie\CookieJar;
use StoreNotifier\Channels\Pushover;
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
            // new ProductData([
            //     'store_product_id' => '68774',
            //     'title' => 'Sigma 24-70mm f/2.8',
            //     'url' => 'https://www.mpb.com/de-de/produkt/sigma-24-70mm-f28-dg-dn-art-sony-e-fit?sort[productLastOnline]=DESC',
            //     'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/c2c3158e-0c1e-44c7-b133-a47adc69b50d',
            // ]),
            new ProductData([
                'store_product_id' => '77405',
                'title' => 'Sigma 16-28mm F/2.8',
                'url' => 'https://www.mpb.com/de-de/produkt/sigma-16-28mm-f2f28-dg-dn-contemporary-sony-e-kompatibel?sort[productLastOnline]=DESC',
                'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/b2fbe187-70ad-4949-884c-d394615cb36c',
            ]),
            new ProductData([
                'store_product_id' => '63828',
                'title' => 'Sony FE 70-200mm f/4',
                'url' => 'https://www.mpb.com/de-de/produkt/sony-fe-70-200mm-f-4-g-oss?sort[productLastOnline]=DESC',
                'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/b9ef6828-ed3a-4950-8001-5ead7be99e6f',
            ]),
            new ProductData([
                'store_product_id' => '66566',
                'title' => 'Samyang AF 14mm f/2.8',
                'url' => 'https://www.mpb.com/de-de/produkt/samyang-14mm-f-2-8-af-fe-fit',
                'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/c136e062-df2e-4caf-b7d5-bfc6a6506488',
            ]),
            new ProductData([
                'store_product_id' => '68433',
                'title' => 'Samyang AF 18mm f/2.8',
                'url' => 'https://www.mpb.com/de-de/produkt/samyang-af-18mm-f-2-8-sony-fe-fit',
                'image_url' => 'https://www.mpb.com/media-service-img-cdn/width=286,quality=90,format=jpeg/media-service/3ea31749-99b3-4c0a-aa0e-fbf35d56ef10',
            ]),
        ];

        foreach ($products as $product) {
            $fullUrl = sprintf($searchUrl, self::getUrl(), $product->store_product_id);
            $fullUrl = str_replace(PHP_EOL, '', $fullUrl);

            $contents = self::newHttpClient()
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
        ];
    }
}
