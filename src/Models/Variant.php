<?php

namespace StoreNotifier\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $store_variant_id
 * @property string $title
 * @property int $price
 * @property bool $available
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \StoreNotifier\Models\Product|null $product
 * @property int|null $products_count
 */
class Variant extends AbstractModel
{
    protected $table = 'variants';

    protected $casts = [
        'available' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPrettyPrice(): string
    {
        return self::prettifyPrice($this->price, $this->currency);
    }

    public static function prettifyPrice(int $price, string $currency): string
    {
        return number_format($price / 100, 2, ',', '.') . ' ' . $currency;
    }
}
