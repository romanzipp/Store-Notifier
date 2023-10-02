<?php

namespace StoreNotifier\Notifications\Concerns;

use StoreNotifier\Channels\Message\MessageItem;
use StoreNotifier\Channels\Message\MessagePayload;
use StoreNotifier\Models\Event;

trait VariantsNotification
{
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

    protected function handleVariants(string $messageLine, int $prio): void
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
            $message .= sprintf($messageLine, "{$variant->title} @ {$variant->getPrettyPrice()}") . PHP_EOL;
        }

        $items = [];
        foreach ($this->variants as $variant) {
            $items[] = new MessageItem($variant->title, $variant->product?->url);
        }

        $title = "{$this->getTitle()} ({$this->provider::getTitle()})";

        $this->send(new MessagePayload(
            message: $message,
            title: $title,
            subtitle: $product->title,
            url: $product->url,
            attachment: $product->image_url,
            prio: $prio,
            items: $items
        ));
    }

    protected function getVariantsTitle(string $soloTitle, string $multiTitle): string
    {
        $count = count($this->variants);

        $firstVariant = null;
        foreach ($this->variants as $variant) {
            $firstVariant = $variant;
            break;
        }

        if (1 === $count) {
            return sprintf($soloTitle, $firstVariant->title);
        }

        return sprintf($multiTitle, $count);
    }
}
