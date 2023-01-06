<?php

namespace StoreNotifier\Notifications;

use StoreNotifier\Providers\AbstractProvider;

class ErrorOccured extends AbstractNotification
{
    public function __construct(
        public AbstractProvider $provider,
        public string $message
    ) {
    }

    protected function handle(): void
    {
        $this->send(
            message: $this->message,
            title: "Error in {$this->provider::getId()}"
        );
    }
}
