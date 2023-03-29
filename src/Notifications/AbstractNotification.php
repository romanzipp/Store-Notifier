<?php

namespace StoreNotifier\Notifications;

use StoreNotifier\Channels\Message\MessagePayload;
use StoreNotifier\Providers\AbstractProvider;

abstract class AbstractNotification
{
    public AbstractProvider $provider;

    public function execute(): void
    {
        $this->log();
        $this->handle();
    }

    abstract protected function handle(): void;

    protected function log(): void
    {
    }

    final protected function send(MessagePayload $message): void
    {
        foreach ($this->provider->getChannels() as $channel) {
            $channel->sendMessage($message);
        }
    }
}
