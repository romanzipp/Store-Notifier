<?php

namespace StoreNotifier\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class Event extends AbstractModel
{
    protected $table = 'events';

    public const TYPE_ERROR = 0;
    public const TYPE_NEW_PRODUCT = 1;
    public const TYPE_NEW_VARIANT = 2;

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
