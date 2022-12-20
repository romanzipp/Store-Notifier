<?php

namespace StoreNotifier\Providers\Data\ModelData;

use romanzipp\DTO\AbstractData;
use romanzipp\DTO\Attributes\Required;
use StoreNotifier\Models\Variant;

class VariantData extends AbstractData
{
    #[Required]
    public string $store_variant_id;

    #[Required]
    public string $title;

    #[Required]
    public int $price;
    public string $currency = 'USD';

    public bool $available;
    public ?int $units_available = null;

    public function getPrettyPrice(): string
    {
        return Variant::prettifyPrice($this->price, $this->currency);
    }
}
