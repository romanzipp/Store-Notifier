<?php

namespace StoreNotifier\Providers;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use StoreNotifier\Models\Product;
use StoreNotifier\Providers\Data\ModelData\ProductData;

abstract class AbstractProvider
{
    abstract public static function getId(): string;

    abstract public function handle(): void;

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        return [
            new BillieEilishUsProvider(),
        ];
    }

    protected static function wrapArray(Response $response, string $dataClass, \Closure $dataCallback): array
    {
        $data = @json_decode($response->getBody()->getContents());
        $dataItems = $dataCallback($data);

        $items = [];
        foreach ($dataItems as $dataItem) {
            $items[] = $dataClass::fromArray((array) $dataItem);
        }

        return $items;
    }

    /**
     * @param \StoreNotifier\Providers\Data\ModelData\ProductData[] $productsData
     *
     * @return void
     */
    protected function storeProducts(array $productsData): void
    {
        $existingProducts = Product::query()
            ->where('provider', static::getId())
            ->whereIn('store_product_id', array_map(fn (ProductData $productData) => $productData->store_product_id, $productsData))
            ->get();

        foreach ($productsData as $productItem) {
            $existingModel = $existingProducts->where('store_product_id', $productItem->store_product_id)->first();

            if (null === $existingModel) {
                Product::query()->create([
                    ...$productItem->toArray(),
                    'provider' => static::getId(),
                    'last_checked_at' => Carbon::now(),
                ]);
            }
        }
    }
}
