<?php

namespace StoreNotifier\Database;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class Database
{
    public static Manager $manager;

    public function __construct()
    {
        self::$manager = new Manager();
        self::$manager->addConnection([
            'driver' => 'sqlite',
            'host' => 'database',
            'database' => 'database/db.sqlite',
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        self::$manager->setAsGlobal();
        self::$manager->bootEloquent();
    }

    public static function getConnection()
    {
        if ( ! isset(self::$manager)) {
            new self();
        }

        return self::$manager;
    }

    public function migrate(): void
    {
        self::$manager::schema()->dropAllTables();

        self::$manager::schema()->create('products', function (Blueprint $table) {
            $table->increments('id');

            $table->string('provider');

            $table->string('store_product_id');
            $table->string('title');
            $table->string('url');
            $table->string('image_url')->nullable();

            $table->timestamp('last_checked_at');

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        self::$manager::schema()->create('variants', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            $table->string('store_variant_id');
            $table->string('title');

            $table->integer('price');
            $table->string('currency');

            $table->boolean('available');
            $table->unsignedInteger('units_available')->nullable();

            $table->timestamps();
        });

        self::$manager::schema()->create('events', function (Blueprint $table) {
            $table->increments('id');

            $table->string('provider');
            $table->unsignedInteger('type');

            $table->text('title');
            $table->text('details')->nullable();

            $table->nullableMorphs('subject');

            $table->timestamps();
        });
    }
}
