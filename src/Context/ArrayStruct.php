<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

abstract class ArrayStruct implements \JsonSerializable
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(protected readonly array $data)
    {
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
