<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
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

    private function getTitle(): string
    {
        return $this->getVariantsTitle(
            'Entfernte Variante: %s',
            '%s entfernte Varianten'
        );
    }
}
