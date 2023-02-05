<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Models\Event;
use StoreNotifier\Providers\AbstractProvider;

class NewVariantsAvailable extends AbstractNotification
{
    public function __construct(
        public AbstractProvider $provider,
        /** @var \StoreNotifier\Models\Variant[] $variants */
        public array $variants
    ) {
    }

    protected function log(): void
    {
        foreach ($this->variants as $variant) {
            $this->provider->logEvent(
                Event::TYPE_NEW_VARIANT,
                $this->getTitle(),
                null,
                $variant
            );
        }
    }

    protected function handle(): void
    {
        if (empty($this->variants)) {
            throw new \RuntimeException('Needs product');
        }

        /** @var \StoreNotifier\Models\Product $product */
        $product = null;
        foreach ($this->variants as $variant) {
            $product = $variant->product;
            break;
        }

        $message = $product->title . PHP_EOL;
        foreach ($this->variants as $variant) {
            $message .= "NEU: {$variant->title} @ {$variant->getPrettyPrice()}" . PHP_EOL;
        }

        $this->send(
            message: $message,
            title: "{$this->getTitle()} ({$this->provider::getTitle()})",
            url: $product->url,
            attachment: $product->image_url,
            prio: Priority::HIGH
        );
    }

    private function getTitle(): string
    {
        $count = count($this->variants);

        $firstVariant = null;
        foreach ($this->variants as $variant) {
            $firstVariant = $variant;
            break;
        }

        if (1 === $count) {
            return "Neue Variante: {$firstVariant->title}";
        }

        return "{$count} neue Varianten";
    }
}
