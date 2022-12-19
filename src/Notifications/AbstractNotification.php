<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Options;
use donatj\Pushover\Priority;
use donatj\Pushover\Pushover;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use StoreNotifier\Providers\AbstractProvider;

abstract class AbstractNotification
{
    public AbstractProvider $provider;

    final public function send(string $message, string $title, string $url, ?string $attachment = null, int $prio = Priority::NORMAL): void
    {
        $client = new Client();

        $attachmentData = null;

        try {
            $attachmentData = Utils::tryFopen($attachment, 'r');
        } catch (\RuntimeException $exception) {
        }

        $params = [
            'message' => $message,
            'title' => $title,
            'priority' => $prio,
            'url' => $url,
            'attachment' => $attachment ? '' : null,
            // ----
            'token' => $_ENV['PUSHOVER_APP_KEY'],
            'user' => $_ENV['PUSHOVER_USER_KEY'],
        ];

        $multipart = array_map(fn ($key) => [
            'name' => $key,
            'contents' => $params[$key],
        ], array_keys($params));

        if ($attachmentData) {
            $multipart[] = [
                'name' => 'attachment',
                'contents' => $attachmentData,
                'filename' => 'image.jpg',
                'headers' => [
                    'Content-Type' => 'image/jpeg',
                ],
            ];
        }

        try {
            $client->post('https://api.pushover.net/1/messages.json', [
                RequestOptions::MULTIPART => $multipart,
            ]);
        } catch (ClientException $exception) {
            throw new class(@json_decode($exception->getResponse()->getBody()->getContents())) extends \Exception {
                public function __construct(\stdClass $json)
                {
                    parent::__construct(
                        json_encode($json, JSON_PRETTY_PRINT)
                    );
                }
            };
        }

        dd();

        $push = new Pushover($_ENV['PUSHOVER_APP_KEY'], $_ENV['PUSHOVER_USER_KEY']);
        $ok = $push->send($message, [
            Options::TITLE => $title,
            Options::URL => $url,
            Options::PRIORITY => $prio,
        ]);

        if ( ! $ok) {
            throw new \Exception('Failed sending push');
        }
    }
}
