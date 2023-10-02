<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Channels\AbstractChannel;
use StoreNotifier\Channels\Telegram;
use StoreNotifier\Notifications\Concerns\VariantsNotification;
use StoreNotifier\Providers\AbstractProvider;

class VariantsRemoved extends AbstractNotification
{
    use VariantsNotification;

    public function __construct(
        public AbstractProvider $provider,
        /** @var \StoreNotifier\Models\Variant[] $variants */
        public array $variants
    ) {
    }

    protected function handle(): void
    {
        $this->handleVariants(
            '🗑️ %s',
            Priority::LOW
        );
    }

    public function skipsChannel(AbstractChannel $channel): bool
    {
        return $channel instanceof Telegram;
    }

    private function getTitle(): string
    {
        return $this->getVariantsTitle(
            'Entfernte Variante: %s',
            '%s entfernte Varianten'
        );
    }
}
