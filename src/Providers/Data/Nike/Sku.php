<?php

namespace StoreNotifier\Providers\Data\Nike;

use romanzipp\DTO\AbstractData;

class Sku extends AbstractData
{
    public string $id;

    public string $merchGroup;

    public string $nikeSize;

    public static function fromApi(\stdClass $data): self
    {
        return new self([
            'id' => $data->id,
            'merchGroup' => $data->merchGroup,
            'nikeSize' => $data->nikeSize,
        ]);
    }
}
