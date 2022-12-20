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
                'Var: %d ... VarAv: %d ... Pr: %s ... %s',
                count($product->variants),
                count(array_filter($product->variants, fn (VariantData $variant) => $variant->available)),
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
