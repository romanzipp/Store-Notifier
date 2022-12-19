<?php

namespace StoreNotifier\Providers;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use StoreNotifier\Models\Product;
use StoreNotifier\Notifications\NewProductsAvailable;
use StoreNotifier\Providers\Data\ModelData\ProductData;

abstract class AbstractProvider
{
    abstract public static function getId(): string;

    abstract public static function getTitle(): string;

    abstract public static function getUrl(): string;

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
        $productIds = array_map(fn (ProductData $productData) => $productData->store_product_id, $productsData);

        /** @var \StoreNotifier\Models\Product[] $existingProducts */
        $existingProducts = [
            ...Product::query()
                ->where('provider', static::getId())
                ->whereIn('store_product_id', $productIds)
                ->get(),
        ];

        /** @var \StoreNotifier\Models\Product[] $removedProducts */
        $removedProducts = [
            ...Product::query()
                ->where('provider', static::getId())
                ->whereNotIn('store_product_id', $productIds)
                ->get(),
        ];

        /** @var \StoreNotifier\Models\Product[] $newProducts */
        $newProducts = [];

        $n = new NewProductsAvailable($this, $existingProducts);
        $n->handle();

        foreach ($productsData as $productItem) {
            $existingModel = [...array_filter($existingProducts, fn (Product $product) => $product->store_product_id === $productItem->store_product_id)][0] ?? null;

            if (null === $existingModel) {
                $data = $productItem->toArray();
                unset($data['variants']);

                /** @var \StoreNotifier\Models\Product $product */
                $newProducts[] = $product = Product::query()->create([
                    ...$data,
                    'provider' => static::getId(),
                    'last_checked_at' => Carbon::now(),
                ]);

                foreach ($productItem->variants as $variantItem) {
                    $product->variants()->updateOrCreate([
                        'store_variant_id' => $variantItem->store_variant_id,
                        'title' => $variantItem->title,
                        'price' => $variantItem->price,
                        'available' => $variantItem->available,
                    ]);
                }
            }
        }
    }
}
