<?php

namespace StoreNotifier\Channels\Message;

class MessageItem
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $url = null,
    ) {
    }
}
