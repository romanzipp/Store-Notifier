<?php

namespace StoreNotifier\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use StoreNotifier\Database\Database;

abstract class AbstractModel extends Model
{
    protected $guarded = [];

    public function getConnection(): Connection
    {
        /** @see \Illuminate\Database\SQLiteConnection */
        return Database::getConnection()->getConnection();
    }
}
