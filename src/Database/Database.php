<?php

namespace StoreNotifier\Database;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class Database
{
    public Manager $manager;

    public function __construct()
    {
        $this->manager = new Manager();
        $this->manager->addConnection([
            'driver' => 'sqlite',
            'host' => 'database',
            'database' => 'database/db.sqlite',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $this->manager->setAsGlobal();
        $this->manager->bootEloquent();
    }

    public function migrate(): void
    {
        $this->manager::schema()->dropAllTables();

        $this->manager::schema()->create('products', function (Blueprint $table) {
            $table->increments('id');

            $table->string('provider');

            $table->string('store_product_id');
            $table->string('title');
            $table->string('url');

            $table->timestamp('last_checked_at');

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $this->manager::schema()->create('variants', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            $table->timestamps();
        });
    }
}
