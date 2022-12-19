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
            'driver' => 'mysql',
            'host' => 'database',
            'database' => 'lemp',
            'username' => 'lemp',
            'password' => 'lemp',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $this->manager->setAsGlobal();
        $this->manager->bootEloquent();
    }

    public function migrate()
    {
        $this->manager::schema()->dropAllTables();

        $this->manager::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');

            $table->string('email')->unique();
            $table->text('password');
            $table->string('name');

            $table->timestamps();
        });
    }
}
