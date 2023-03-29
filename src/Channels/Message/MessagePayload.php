<?php

namespace StoreNotifier\Channels\Message;

use donatj\Pushover\Priority;

class MessagePayload
{
    /**
     * @param string $message
     * @param string $title
     * @param string|null $url
     * @param string|null $attachment
     * @param int $prio
     * @param \StoreNotifier\Channels\Message\MessageItem[] $items
     */
    public function __construct(
        public readonly string $message,
        public readonly string $title,
        public readonly ?string $subtitle = null,
        public readonly ?string $url = null,
        public readonly ?string $attachment = null,
        public readonly int $prio = Priority::NORMAL,
        public array $items = []
    ) {
    }
}
