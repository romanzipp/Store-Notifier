<?php

namespace StoreNotifier\Providers\Data\Shopify;

use romanzipp\DTO\AbstractData;

class Product extends AbstractData
{
    public int $id; // 3928586125373
    public string $title; // "BILLIE \"WHITE SHIRT\""
    public string $handle; // "billie-white-shirt"
    public string $body_html; // "BILLIE \"WHITE SHIRT\""
    public string $published_at; // "2022-08-18T20:46:56-04:00"
    public string $created_at; // "2019-07-12T14:33:16-04:00"
    public string $updated_at; // "2022-12-18T20:55:16-05:00"
    public string $vendor; // "Billie Eilish | Store"
    public string $product_type; // "T-Shirt"

    /**
     * @var string[]
     */
    public array $tags;

    /**
     * @var \StoreNotifier\Providers\Data\Shopify\Variant[]
     */
    public array $variants;

    /**
     * @var \StoreNotifier\Providers\Data\Shopify\Image[]
     */
    public array $images;

    /**
     * @var \StoreNotifier\Providers\Data\Shopify\Option[]
     */
    public array $options;

    public static function fromArray(array $data = []): static
    {
        $instance = parent::fromArray($data);
        $instance->variants = array_map(fn ($data) => Variant::fromArray((array) $data), $data['variants']);
        $instance->options = array_map(fn ($data) => Option::fromArray((array) $data), $data['options']);
        $instance->images = array_map(fn ($data) => Image::fromArray((array) $data), $data['images']);

        return $instance;
    }
}
