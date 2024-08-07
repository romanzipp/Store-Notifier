<?php

namespace StoreNotifier\Channels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use StoreNotifier\Channels\Message\MessagePayload;

final class Telegram extends AbstractChannel
{
    public const TYPE_PRIMARY = 'PRIMARY';
    public const TYPE_SECONDARY = 'SECONDARY';
    public const TYPE_TILL = 'TILL';

    public function __construct(
        public string $type
    ) {
    }

    public function sendMessage(MessagePayload $message): void
    {
        $apiKey = $_ENV['TELEGRAM_' . $this->type . '_API_KEY'] ?? null;
        $channel = $_ENV['TELEGRAM_' . $this->type . '_CHANNEL'] ?? null;

        if ( ! $apiKey || ! $channel) {
            return;
        }

        $client = new Client();

        $text = sprintf('✨ *%s* ✨', $message->title);

        if ($message->subtitle) {
            $text .= "\n\n👕 {$message->subtitle}";
        }

        $text .= "\n\n";

        if ( ! empty($message->items)) {
            foreach ($message->items as $item) {
                $text .= sprintf('- [%s](%s)', $item->name, $item->url ?? $message->url);
                $text .= "\n";
            }
        } else {
            $text .= $message->message;
            $text .= "\n\n";
            $text .= $message->url;
        }

        try {
            $client->post("https://api.telegram.org/bot{$apiKey}/sendMessage", [
                RequestOptions::QUERY => [
                    'text' => Str::limit($text, 3500),
                    'parse_mode' => 'markdown',
                    'chat_id' => $channel,
                ],
            ]);
        } catch (ClientException $exception) {
            throw new \Exception(($data = @json_decode($exception->getResponse()->getBody()->getContents())) ? json_encode($data, JSON_PRETTY_PRINT) : $exception->getMessage());
        }
    }
}
