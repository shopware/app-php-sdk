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

    public function isset(string $property): bool
    {
        return isset($this->data[$property]);
    }

    public function isNull(string $property): bool
    {
        $data = array_key_exists($property, $this->data) ? $this->data[$property] : true;

        return $data === null;
    }
}
