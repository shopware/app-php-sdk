<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class LineItem extends ArrayStruct
{
    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getUniqueIdentifier(): string
    {
        \assert(is_string($this->data['uniqueIdentifier']));
        return $this->data['uniqueIdentifier'];
    }

    public function getType(): string
    {
        \assert(is_string($this->data['type']));
        return $this->data['type'];
    }

    public function getReferencedId(): string
    {
        \assert(is_string($this->data['referencedId']));
        return $this->data['referencedId'];
    }

    public function getLabel(): string
    {
        \assert(is_string($this->data['label']));
        return $this->data['label'];
    }

    public function getDescription(): ?string
    {
        \assert(is_string($this->data['description']) || is_null($this->data['description']));
        return $this->data['description'];
    }

    public function isGood(): bool
    {
        \assert(is_bool($this->data['good']));
        return $this->data['good'];
    }

    public function getQuantity(): int
    {
        \assert(is_int($this->data['quantity']));
        return $this->data['quantity'];
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        \assert(is_array($this->data['payload']));
        return $this->data['payload'];
    }

    public function getPrice(): CalculatedPrice
    {
        \assert(is_array($this->data['price']));
        return new CalculatedPrice($this->data['price']);
    }

    /**
     * @return array<string>
     */
    public function getStates(): array
    {
        \assert(is_array($this->data['states']));

        return $this->data['states'];
    }

    /**
     * @return array<LineItem>
     */
    public function getChildren(): array
    {
        \assert(is_array($this->data['children']));

        return array_map(static fn (array $child): LineItem => new LineItem($child), $this->data['children']);
    }
}
