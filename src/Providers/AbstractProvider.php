<?php

namespace StoreNotifier\Providers;

use Campo\UserAgent;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use StoreNotifier\Log\Logger;
use StoreNotifier\Models\AbstractModel;
use StoreNotifier\Models\Event;
use StoreNotifier\Models\Product;
use StoreNotifier\Models\Variant;
use StoreNotifier\Notifications\NewProductsAvailable;
use StoreNotifier\Notifications\NewVariantsAvailable;
use StoreNotifier\Notifications\VariantsRemoved;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

abstract class AbstractProvider
{
    public const PRESET_HIGH_PRIO = 'prio';
    public const PRESET_LONG_RUNNING = 'lame';

    public static bool $dryRun = false;

    public Logger $logger;

    abstract public static function getId(): string;

    abstract public static function getTitle(): string;

    abstract public static function getUrl(): string;

    abstract public function handle(): void;

    /**
     * @return \StoreNotifier\Channels\AbstractChannel[]
     */
    abstract public function getChannels(): array;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->logger->provider = $this;
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        return [
            new MpbProvider(),
            new KummerProvider(),
            new KraftklubProvider(),
            new GirlInRedUsProvider(),
            new FinneasProvider(),
            new BringMeTheHorizonProvider(),
            new NikeProvider(),
            new PhoebeBridgersUkProvider(),
            new PhoebeBridgersUsProvider(),
            new BillieEilishUkProvider(),
            new BillieEilishUsProvider(),
            new BillieEilishDeProvider(),
        ];
    }

    public static function getPresets(): array
    {
        return [
            self::PRESET_HIGH_PRIO => [
                new MpbProvider(),
                new KummerProvider(),
                new KraftklubProvider(),
                new GirlInRedUsProvider(),
                new FinneasProvider(),
                new BringMeTheHorizonProvider(),
                new NikeProvider(),
                new PhoebeBridgersUkProvider(),
                new PhoebeBridgersUsProvider(),
                new BillieEilishUkProvider(),
                new BillieEilishUsProvider(),
            ],
            self::PRESET_LONG_RUNNING => [
                new BillieEilishDeProvider(),
            ],
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
        $productStoreIds = array_map(fn (ProductData $productData) => $productData->store_product_id, $productsData);
        $variantIds = [];

        foreach ($productsData as $product) {
            foreach ($product->variants  as $variant) {
                $variantIds[] = $variant->store_variant_id;
            }
        }

        /** @var \StoreNotifier\Models\Product[] $existingProducts */
        $existingProducts = [
            ...Product::query()
                ->where('provider', static::getId())
                ->whereIn('store_product_id', $productStoreIds)
                ->get(),
        ];

        $productIds = array_map(fn (Product $product) => $product->id, $existingProducts);

        /** @var \StoreNotifier\Models\Variant[] $removedVariants */
        $removedVariants = [
            ...Variant::query()
                ->where('available', true)
                ->whereIn('product_id', $productIds)
                ->whereNotIn('store_variant_id', $variantIds)
                ->get(),
        ];

        if ( ! empty($removedVariants)) {
            (new VariantsRemoved($this, $removedVariants))->execute();

            foreach ($removedVariants as $removedVariant) {
                $removedVariant->update([
                    'available' => false,
                ]);
            }
        }

        // /** @var \StoreNotifier\Models\Product[] $removedProducts */
        // $removedProducts = [
        //     ...Product::query()
        //         ->where('provider', static::getId())
        //         ->whereNotIn('store_product_id', $productStoreIds)
        //         ->get(),
        // ];
        //
        // if( ! empty($removedProducts)) {
        //     (new ProductsRemoved($this, $removedProducts))->execute();
        //
        //     foreach ($removedProducts as $removedProduct) {
        //         $removedProduct->delete();
        //     }
        // }

        /** @var \StoreNotifier\Models\Product[] $newProducts */
        $newProducts = [];

        foreach ($productsData as $productItem) {
            $data = $productItem->toArray();
            $data['provider'] = static::getId();
            $data['last_checked_at'] = Carbon::now();
            unset($data['store_product_id']);
            unset($data['variants']);

            $product = self::$dryRun
                ? new Product(array_merge($data, ['store_product_id' => $productItem->store_product_id]))
                : Product::query()->updateOrCreate([
                    'store_product_id' => $productItem->store_product_id,
                ], $data);

            if (self::$dryRun) {
                $product->wasRecentlyCreated = 0 === Product::query()->where('store_product_id', $productItem->store_product_id)->count();
            }

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
                $variantData = [
                    'title' => $variantItem->title,
                    'price' => $variantItem->price,
                    'currency' => $variantItem->currency,
                    'available' => $variantItem->available,
                    'units_available' => $variantItem->units_available,
                ];

                $variants[] = self::$dryRun
                    ? new Variant(array_merge($variantData, ['store_variant_id' => $variantItem->store_variant_id]))
                    : $product->variants()->updateOrCreate([
                        'store_variant_id' => $variantItem->store_variant_id,
                    ], $variantData);
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
                $notification->execute();
            }
        }

        if ( ! empty($newProducts)) {
            $notification = new NewProductsAvailable($this, $newProducts);
            $notification->execute();
        }
    }

    public function logEvent(int $type, string $title, ?string $details = null, ?AbstractModel $subject = null): void
    {
        /** @var \StoreNotifier\Models\Event $event */
        $event = Event::query()->make([
            'provider' => static::getId(),
            'type' => $type,
            'title' => $title,
            'details' => $details,
        ]);

        if (null !== $subject) {
            $event->subject()->associate($subject);
        }

        $event->save();
    }

    public function hasRecenctError(): bool
    {
        return Event::query()
            ->where('provider', static::getId())
            ->where('created_at', '>', Carbon::now()->subHour())
            ->count() > 0;
    }

    protected static function newHttpClient(array $mergeConfig = []): Client
    {
        $requestOptions = $mergeConfig;
        $requestOptions[RequestOptions::TIMEOUT] = 15;

        if ( ! isset($requestOptions[RequestOptions::HEADERS])) {
            $requestOptions[RequestOptions::HEADERS] = [];
        }

        $requestOptions[RequestOptions::HEADERS]['User-Agent'] = UserAgent::random();
        $requestOptions[RequestOptions::HEADERS]['X-Forwarded-For'] = sprintf('%d.%d.%d.%d', rand(11, 254), rand(1, 255), rand(1, 255), rand(1, 255));

        return new Client($requestOptions);
    }
}
