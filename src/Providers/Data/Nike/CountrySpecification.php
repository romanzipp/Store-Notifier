<?php

namespace StoreNotifier\Providers\Data\Nike;

use romanzipp\DTO\AbstractData;

class CountrySpecification extends AbstractData
{
    public string $country;

    public string $localizedSize;

    public static function fromApi(\stdClass $data):self
    {
        return new self([
            'country' => $data->country,
            'localizedSize' => $data->localizedSize,
        ]);
    }
}
