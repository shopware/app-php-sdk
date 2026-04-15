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

    /**
     * Associations or fields might be null or not set
     */
    public function isset(string $property, bool $allowNull = false): bool
    {
        if ($allowNull) {
            return array_key_exists($property, $this->data);
        }

        return ($this->data[$property] ?? null) !== null;
    }
}
