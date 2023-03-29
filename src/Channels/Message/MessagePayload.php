<?php

namespace StoreNotifier\Channels\Message;

use donatj\Pushover\Priority;

class MessagePayload
{
    public function __construct(
        public readonly string $message,
        public readonly string $title,
        public readonly ?string $url = null,
        public readonly ?string $attachment = null,
        public readonly int $prio = Priority::NORMAL
    ) {
    }
}
