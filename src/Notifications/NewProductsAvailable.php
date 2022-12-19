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
        $url = $this->provider::getUrl();
        $image = null;

        if (($count = count($this->products)) > 1) {
            $title = "{$count} neue Produkte verfügbar";

            foreach ($this->products as $product) {
                $image = $product->image_url;
                $url = $product->url;
                break;
            }
        }

        $message = '';
        foreach ($this->products as $product) {
            $message .= $product->title . PHP_EOL;
        }

        $this->send(
            message: $message,
            title: "{$title} @ {$this->provider::getTitle()}",
            prio: Priority::HIGH,
            url: $url,
            attachment: $image
        );
    }
}
