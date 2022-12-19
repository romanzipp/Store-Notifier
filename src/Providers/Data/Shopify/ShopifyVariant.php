<?php

namespace StoreNotifier\Providers\Data\Shopify;

use romanzipp\DTO\AbstractData;

class ShopifyVariant extends AbstractData
{
    public int $id; // 29392462938173

    public string $title; // "S"
    public ?string $option1; // "S"
    public ?string $option2; // null
    public ?string $option3; // null
    public string $sku; // "838302063"
    public bool $requires_shipping; // true
    public bool $taxable; // true
    public ?string $featured_image; // null
    public bool $available; // false
    public string $price; // "30.00"
    public int $grams; // 200
    public ?bool $compare_at_price; // null
    public int $position; // 1
    public int $product_id; // 3928586125373

    public string $created_at; // "2019-07-12T14:33:16-04:00"
    public string $updated_at; // "2022-11-29T11:59:01-05:00"
}
