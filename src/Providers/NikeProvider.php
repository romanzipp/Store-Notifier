<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;
use StoreNotifier\Providers\Data\Nike\CountrySpecification;
use StoreNotifier\Providers\Data\Nike\Size;
use StoreNotifier\Providers\Data\Nike\Sku;

class NikeProvider extends AbstractProvider
{
    public static function getId(): string
    {
        return 'nike';
    }

    public static function getTitle(): string
    {
        return 'Nike';
    }

    public static function getUrl(): string
    {
        return 'https://nike.com';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }

    private function getMonitoredProducts(): array
    {
        return [];

        return [
            [
                'url' => 'https://api.nike.com/product_feed/threads/v2?filter=language(de)&filter=marketplace(DE)&filter=channelId(d9a5bc42-4b9c-4976-858a-f159cf99c647)&filter=productInfo.merchProduct.styleColor(DR9513-100)',
                'sizes' => [
                    '43',
                ],
            ],
        ];
    }

    public function handle(): void
    {
        // https://api.nike.com/product_feed/threads/v2
        //    ?filter=language(de)
        //    &filter=marketplace(DE)
        //    &filter=channelId(d9a5bc42-4b9c-4976-858a-f159cf99c647)
        //    &filter=productInfo.merchProduct.styleColor(DV7585-200,DQ7668-100,FJ2895-100,DZ7338-001,DH5623-101,DM0107-500,DQ7558-101,DQ7659-101,DQ7584-100,DH2933-101,CW2289-111,DZ4512-100)

        $products = [];

        foreach ($this->getMonitoredProducts() as $monitoredProduct) {
            $content = self::newHttpClient()
                ->get($monitoredProduct['url'])
                ->getBody()
                ->getContents();

            $data = json_decode($content);

            if (empty($data->objects)) {
                continue;
            }

            $object = [...$data->objects][0];

            if (empty($object->productInfo)) {
                continue;
            }

            $productInfo = [...$object->productInfo][0];

            $skus = $productInfo->skus;
            $availableSkus = $productInfo->availableSkus;

            $product = new ProductData([
                'store_product_id' => $object->id,
                'title' => $object->publishedContent->properties->title,
                'url' => "https://www.nike.com/de/t/{$object->publishedContent->properties->seo->slug}",
                'image_url' => $object->publishedContent->properties->productCard->properties->portraitURL,
            ]);

            /** @var array<string, \StoreNotifier\Providers\Data\Nike\Size[]> $sizes */
            $sizes = [];

            foreach ($skus as $sku) {
                $euSpecification = null;
                foreach ($sku->countrySpecifications as $countrySpecification) {
                    if ('DE' === $countrySpecification->country) {
                        $euSpecification = $countrySpecification;
                        break;
                    }
                }

                if ( ! $euSpecification) {
                    dd('coundl find spec');
                }

                if (in_array($euSpecification->localizedSize, $monitoredProduct['sizes'])) {
                    $sizes[$sku->id] = new Size([
                        'sku' => $sku = Sku::fromApi($sku),
                        'countrySpecification' => CountrySpecification::fromApi($euSpecification),
                    ]);
                }
            }

            foreach ($availableSkus as $availableSku) {
                $size = $sizes[$availableSku->id] ?? null;

                if (null === $size) {
                    continue;
                }

                $size->available = $availableSku->available;

                $product->variants[] = new VariantData([
                    'store_variant_id' => $size->sku->id,
                    'title' => $size->countrySpecification->localizedSize,
                    'price' => (int) ($productInfo->merchPrice->currentPrice * 100),
                    'currency' => $productInfo->merchPrice->currency,
                    'available' => $size->available,
                    'units_available' => null,
                ]);
            }

            $products[] = $product;
        }

        $this->storeProducts($products);
    }
}
