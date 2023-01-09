<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Models\Event;
use StoreNotifier\Models\Variant;
use StoreNotifier\Providers\AbstractProvider;

class NewProductsAvailable extends AbstractNotification
{
    public function __construct(
        public AbstractProvider $provider,
        /** @var \StoreNotifier\Models\Product[] $products */
        public array $products
    ) {
    }

    protected function log(): void
    {
        foreach ($this->products     as $product) {
            $this->provider->logEvent(
                Event::TYPE_NEW_PRODUCT,
                $this->getTitle(),
                null,
                $product
            );
        }
    }

    protected function handle(): void
    {
        $url = null;
        $image = null;

        foreach ($this->products as $product) {
            ! $image && ($image = $product->image_url);
            ! $url && ($url = $product->url);
        }

        if (($count = count($this->products)) > 1) {
            $url = $this->provider::getUrl();
        }

        $message = '';
        foreach ($this->products as $product) {
            $messageLine = $product->title;

            $variants = [...$product->availableVariants];
            if ( ! empty($variants)) {
                $messageLine .= ' (';
                $messageLine .= implode(', ', array_map(fn (Variant $variant) => $variant->title, $variants));
                $messageLine .= ')';
            }

            $messageLine .= PHP_EOL;
            $message .= $messageLine;
        }

        $this->send(
            message: $message,
            title: "{$this->getTitle()} @ {$this->provider::getTitle()}",
            url: $url,
            attachment: $image,
            prio: Priority::HIGH
        );
    }

    private function getTitle(): string
    {
        if (($count = count($this->products)) > 1) {
            return "{$count} neue Produkte";
        }

        return 'NEUES PRODUKT';
    }
}
