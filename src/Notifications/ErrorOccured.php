<?php

namespace StoreNotifier\Notifications;

use StoreNotifier\Channels\Message\MessagePayload;
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
        $this->send(new MessagePayload(
            message: $this->message,
            title: "Error in {$this->provider::getId()}"
        ));
    }
}
