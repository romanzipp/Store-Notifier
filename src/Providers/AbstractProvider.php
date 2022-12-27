<?php

namespace StoreNotifier\Providers;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use StoreNotifier\Models\Product;
use StoreNotifier\Models\Variant;
use StoreNotifier\Notifications\NewProductsAvailable;
use StoreNotifier\Notifications\NewVariantsAvailable;
use StoreNotifier\Providers\Concerns\SendsLogs;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

abstract class AbstractProvider
{
    use SendsLogs;

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
            new PhoebeBridgersUkProvider(),
            new PhoebeBridgersUsProvider(),
            new BillieEilishUkProvider(),
            new BillieEilishUsProvider(),
            new BillieEilishDeProvider(),
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

        foreach ($productsData as $productItem) {
            $data = $productItem->toArray();
            $data['provider'] = static::getId();
            $data['last_checked_at'] = Carbon::now();
            unset($data['store_product_id']);
            unset($data['variants']);

            $product = Product::query()->updateOrCreate([
                'store_product_id' => $productItem->store_product_id,
            ], $data);

            // Variants

            /** @var \StoreNotifier\Models\Variant[] $existingNewlyAvailableVariants */
            $existingNewlyAvailableVariants = $product
                ->variants()
                ->where('available', false)
                ->whereIn(
                    'store_variant_id',
                    array_map(
                        fn (VariantData $variant) => $variant->store_variant_id,
                        array_filter($productItem->variants, fn (VariantData $variant) => $variant->available)
                    )
                )
                ->get();

            /** @var \StoreNotifier\Models\Variant[] $variants */
            $variants = [];

            foreach ($productItem->variants as $variantItem) {
                $variants[] = $product->variants()->updateOrCreate([
                    'store_variant_id' => $variantItem->store_variant_id,
                ], [
                    'title' => $variantItem->title,
                    'price' => $variantItem->price,
                    'currency' => $variantItem->currency,
                    'available' => $variantItem->available,
                    'units_available' => $variantItem->units_available,
                ]);
            }

            if ($product->wasRecentlyCreated) {
                $newProducts[] = $product;

                continue;
            }

            $newVariants = array_filter($variants, fn (Variant $variant) => $variant->wasRecentlyCreated);

            $notifyVaraints = [
                ...$existingNewlyAvailableVariants,
                ...$newVariants,
            ];

            if ( ! empty($notifyVaraints)) {
                $notification = new NewVariantsAvailable($this, $notifyVaraints);
                $notification->handle();
            }
        }

        if ( ! empty($newProducts)) {
            $notification = new NewProductsAvailable($this, $newProducts);
            $notification->handle();
        }
    }
}
