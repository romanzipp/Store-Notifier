<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;

class KummerProvider extends AbstractKrasserStoffProvider
{
    public static function getId(): string
    {
        return 'kummer';
    }

    public static function getTitle(): string
    {
        return 'Kummer';
    }

    protected static function getCategory(): string
    {
        return 'kummer';
    }

    protected static function getShop(): string
    {
        return 'krasserstoff';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }
}
