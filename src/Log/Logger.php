<?php

namespace StoreNotifier\Log;

use Carbon\Carbon;
use Illuminate\Support\Str;
use StoreNotifier\Providers\AbstractProvider;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

class Logger
{
    public function __construct(
        public ?AbstractProvider $provider = null
    ) {
    }

    public function logProduct(ProductData $product): void
    {
        $this->log(
            sprintf(
                'Var: %s ... VarAv: %s ... Pr: %s ... %s',
                Str::padRight(count($product->variants), 3),
                Str::padRight(count(array_filter($product->variants, fn (VariantData $variant) => $variant->available)), 3),
                Str::padRight(($variant = $product->getFirstVariant()) ? $variant->getPrettyPrice() : '-', 10),
                $product->title
            ),
        );
    }

    public function log(string $line): void
    {
        echo sprintf(
            '%s [%s] %s',
            Carbon::now()->format('Y-m-d H:i:s'),
            Str::padBoth($this->provider ? $this->provider::getId() : '....', 13),
            $line
        ) . PHP_EOL;
    }
}
