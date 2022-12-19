<?php

namespace StoreNotifier\Providers\Data\Shopify;

use romanzipp\DTO\AbstractData;

class Option extends AbstractData
{
    public string $name;
    public int $position;

    /**
     * @var string[]
     */
    public array $values;
}
