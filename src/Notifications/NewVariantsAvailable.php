<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use StoreNotifier\Notifications\Concerns\VariantsNotification;
use StoreNotifier\Providers\AbstractProvider;

class NewVariantsAvailable extends AbstractNotification
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
            'NEU: %s',
            Priority::LOW
        );
    }

    private function getTitle(): string
    {
        return $this->getVariantsTitle(
            'Neue Variante: %s',
            '%s neue Varianten'
        );
    }
}
