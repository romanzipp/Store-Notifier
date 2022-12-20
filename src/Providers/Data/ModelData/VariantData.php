<?php

namespace StoreNotifier\Providers\Data\ModelData;

use romanzipp\DTO\AbstractData;

class VariantData extends AbstractData
{
    public string $store_variant_id;
    public string $title;
    public int $price;

    public bool $available;
    public ?int $units_available=null;
}
