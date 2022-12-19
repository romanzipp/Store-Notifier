<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Psr7\Response;

abstract class AbstractProvider
{
    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        return [
            new BillieEilishUsProvider(),
        ];
    }

    protected static function wrapArray(Response $response, string $dataClass, \Closure $dataCallback)
    {
        $data = @json_decode($response->getBody()->getContents());
        $dataItems = $dataCallback($data);

        $items = [];
        foreach ($dataItems as $dataItem) {
            $items[]
                = $dataClass::fromArray((array) $dataItem);
        }

        return $items;
    }
}
