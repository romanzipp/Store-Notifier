<?php

namespace StoreNotifier\Providers\Data\ModelData;

use romanzipp\DTO\AbstractData;
use romanzipp\DTO\Attributes\Required;

class ProductData extends AbstractData
{
    public string $id;
    public string $provider;

    #[Required]
    public string $store_product_id;

    #[Required]
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

    public function getFirstVariant(): ?VariantData
    {
        foreach ($this->variants as $variant) {
            return $variant;
        }

        return null;
    }
}
