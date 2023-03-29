<?php

namespace StoreNotifier\Channels;

use StoreNotifier\Channels\Message\MessagePayload;

abstract class AbstractChannel
{
    abstract public function sendMessage(MessagePayload $message): void;
}
