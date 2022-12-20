<?php

namespace StoreNotifier\Providers\Data\ModelData;

use romanzipp\DTO\AbstractData;
use StoreNotifier\Models\Variant;

class VariantData extends AbstractData
{
    public string $store_variant_id;
    public string $title;

    public int $price;
    public string $currency = 'USD';

    public bool $available;
    public ?int $units_available = null;

    public function getPrettyPrice(): string
    {
        return Variant::prettifyPrice($this->price, $this->currency);
    }
}
