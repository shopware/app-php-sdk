<?php

declare(strict_types=1);

namespace Shopware\App\SDK\TaxProvider;

class CalculatedTax implements \JsonSerializable
{
    public function __construct(
        public readonly float $tax,
        public readonly float $taxRate,
        public readonly float $price,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }

    public function add(CalculatedTax $tax): self
    {
        return new self(
            $this->tax + $tax->tax,
            $this->taxRate,
            $this->price + $tax->price,
        );
    }
}
