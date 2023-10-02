<?php

namespace StoreNotifier\Notifications;

use StoreNotifier\Channels\AbstractChannel;
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

    public function skipsChannel(AbstractChannel $channel): bool
    {
        return false;
    }

    protected function log(): void
    {
    }

    final protected function send(MessagePayload $message): void
    {
        foreach ($this->provider->getChannels() as $channel) {
            if ($this->skipsChannel($channel)) {
                continue;
            }

            $channel->sendMessage($message);
        }
    }
}
