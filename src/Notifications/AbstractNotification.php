<?php

namespace StoreNotifier\Notifications;

use donatj\Pushover\Priority;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
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
        $client = new Client();

        $attachmentData = null;

        if ($attachment) {
            $image = new \Imagick();
            $image->readImageBlob(
                file_get_contents($attachment)
            );

            $image->thumbnailImage(600, 600, false, false);
            $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $image->setImageCompressionQuality(85);
            $image->stripImage();

            $attachmentData = $image->getImageBlob();
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
            throw new \Exception(($data = @json_decode($exception->getResponse()->getBody()->getContents())) ? json_encode($data, JSON_PRETTY_PRINT) : $exception->getMessage());
        }
    }
}
