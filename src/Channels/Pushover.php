<?php

namespace StoreNotifier\Channels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use StoreNotifier\Channels\Message\MessagePayload;

final class Pushover extends AbstractChannel
{
    public function sendMessage(MessagePayload $message): void
    {
        $token = $_ENV['PUSHOVER_APP_KEY'] ?? null;
        $user = $_ENV['PUSHOVER_USER_KEY'] ?? null;

        if ( ! $token || ! $user) {
            return;
        }

        $client = new Client();

        $attachmentData = null;

        if ($message->attachment) {
            try {
                $image = new \Imagick();
                $image->readImageBlob(
                    file_get_contents($message->attachment)
                );

                $image->thumbnailImage(600, 600, false, false);
                $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $image->setImageCompressionQuality(85);
                $image->stripImage();

                $attachmentData = $image->getImageBlob();
            } catch (\Throwable $exception) {
            }
        }

        $params = [
            'message' => $message->message,
            'title' => $message->title,
            'priority' => $message->prio,
            'url' => $message->url,
            'attachment' => $message->attachment ? '' : null,
            // ----
            'token' => $token,
            'user' => $user,
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
