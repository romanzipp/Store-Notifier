<?php

namespace StoreNotifier\Providers\Concerns;

use Illuminate\Support\Str;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

/**
 * @mixin  \StoreNotifier\Providers\AbstractProvider
 */
trait SendsLogs
{
    protected static function logProduct(ProductData $product): void
    {
        self::log(
            sprintf(
                'Var: %s ... VarAv: %s ... Pr: %s ... %s',
                Str::padRight(count($product->variants), 3),
                Str::padRight(count(array_filter($product->variants, fn (VariantData $variant) => $variant->available)), 3),
                Str::padRight(($variant = $product->getFirstVariant()) ? $variant->getPrettyPrice() : '-', 10),
                $product->title
            )
        );
    }

    protected static function log(string $line): void
    {
        echo sprintf(
            '[%s] %s',
            Str::padBoth(static::getId(), 13),
            $line
        ) . PHP_EOL;
    }
}
