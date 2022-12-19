<?php

namespace StoreNotifier\Models;

class Variant extends AbstractModel
{
    protected $table = 'variants';

    protected $casts = [
        'available' => 'boolean',
    ];
}
