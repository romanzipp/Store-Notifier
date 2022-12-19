<?php

namespace StoreNotifier\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends AbstractModel
{
    public $table = 'products';

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }
}
