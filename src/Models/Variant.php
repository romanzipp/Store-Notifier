<?php

namespace StoreNotifier\Models;

/**
 * @property int $id
 * @property int $product_id
 * @property string $store_variant_id
 * @property string $title
 * @property int $price
 * @property bool $available
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Variant extends AbstractModel
{
    protected $table = 'variants';

    protected $casts = [
        'available' => 'boolean',
    ];
}
