<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Providers\AbstractProvider;

class NewVariantsAvailable extends AbstractNotification
{
    public function __construct(
        public AbstractProvider $provider,
        /** @var \StoreNotifier\Models\Variant[] $variants */
        public array $variants
    ) {
    }

    public function handle(): void
    {
        /** @var \StoreNotifier\Models\Product $product */
        $product = null;
        $firstVariant = null;
        foreach ($this->variants as $variant) {
            $firstVariant = $variant;
            $product = $variant->product;
            break;
        }

        if (null === $product || null === $firstVariant) {
            throw new \RuntimeException('Needs product');
        }

        $count = count($this->variants);
        $title = "{$count} neue Varianten";
        if (1 === $count) {
            $title = "Neue Variante '{$firstVariant->title}'";
        }

        $message = $product->title . PHP_EOL . PHP_EOL;
        foreach ($this->variants as $variant) {
            $message .= "{$variant->title} ({$variant->getPrettyPrice()})";
        }

        $this->send(
            message: $message,
            title: "{$title} für '{$product->title}' @ {$this->provider::getTitle()}",
            url: $product->url,
            attachment: $product->image_url,
            prio: Priority::HIGH
        );
    }
}
