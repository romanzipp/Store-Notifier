<?php

namespace StoreNotifier\Providers\Data\Nike;

use romanzipp\DTO\AbstractData;

class Size extends AbstractData
{
    public Sku $sku;

    public CountrySpecification $countrySpecification;

    public ?bool $available = null;
}
