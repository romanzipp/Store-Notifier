<?php

namespace StoreNotifier\Providers\Data\ModelData;

use romanzipp\DTO\AbstractData;

class ProductData extends AbstractData
{
    public string $id;

    public string $provider;

    public string $store_product_id;
    public string $title;
    public string $url;
    public string $image_url;

    public string $published_at;
    public string $created_at;
    public string $updated_at;

    /**
     * @var \StoreNotifier\Providers\Data\ModelData\VariantData[]
     */
    public array $variants = [];
}
