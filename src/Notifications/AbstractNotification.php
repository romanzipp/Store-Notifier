<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
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

    final protected function send(
        string $message,
        string $title,
        ?string $url = null,
        ?string $attachment = null,
        int $prio = Priority::NORMAL
    ): void {
        foreach ($this->provider->getChannels() as $channel) {
            $channel->sendMessage(
                new MessagePayload(
                    $message,
                    $title,
                    $url,
                    $attachment,
                    $prio
                )
            );
        }
    }
}
