<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Providers\AbstractProvider;

class NewProductsAvailable extends AbstractNotification
{
    public function __construct(
        public AbstractProvider $provider,
        /** @var \StoreNotifier\Models\Product[] $products */
        public array $products
    ) {
    }

    public function handle(): void
    {
        $title = 'Neues Produkt verfügbar';
        $url = null;
        $image = null;

        foreach ($this->products as $product) {
            ! $image && ($image = $product->image_url);
            ! $url = $product->url;
            break;
        }

        if (($count = count($this->products)) > 1) {
            $title = "{$count} neue Produkte verfügbar";
            $url = $this->provider::getUrl();
        }

        $message = '';
        foreach ($this->products as $product) {
            $message .= $product->title . PHP_EOL;
        }

        $this->send(
            message: $message,
            title: "{$title} @ {$this->provider::getTitle()}",
            url: $url,
            attachment: $image,
            prio: Priority::HIGH
        );
    }
}
