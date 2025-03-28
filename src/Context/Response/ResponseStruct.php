<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Response;

use JsonSerializable;

abstract class ResponseStruct implements \JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = \get_object_vars($this);

        foreach ($data as $key => $value) {
            if ($value instanceof JsonSerializable) {
                $data[$key] = $value->jsonSerialize();
            }
        }

        return $data;
    }
}
