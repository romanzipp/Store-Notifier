<?php

namespace StoreNotifier\Providers;

use StoreNotifier\Channels\Pushover;

class KraftklubProvider extends AbstractKrasserStoffProvider
{
    public static function getId(): string
    {
        return 'kraftklub';
    }

    public static function getTitle(): string
    {
        return 'Kraftklub';
    }

    protected static function getCategory(): string
    {
        return 'kraftklub-kargo';
    }

    protected static function getShop(): string
    {
        return 'kraftklub-kargo';
    }

    public function getChannels(): array
    {
        return [
            new Pushover(),
        ];
    }
}
