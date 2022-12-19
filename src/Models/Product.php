<?php

namespace StoreNotifier\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $provider
 * @property string $store_product_id
 * @property string $title
 * @property string $url
 * @property string|null $image_url
 * @property string $last_checked_at
 * @property string|null $published_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\StoreNotifier\Models\Variant[] $variants
 * @property int|null $variants_count
 * @property \Illuminate\Database\Eloquent\Collection|\StoreNotifier\Models\Variant[] $availableVariants
 * @property int|null $available_variants_count
 */
class Product extends AbstractModel
{
    public $table = 'products';

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function availableVariants(): HasMany
    {
        return $this->variants()->where('available', true);
    }
}
