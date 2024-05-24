<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Framework\Collection;

class CalculatedPrice extends ArrayStruct
{
    public function getUnitPrice(): float
    {
        \assert(is_float($this->data['unitPrice']) || is_int($this->data['unitPrice']));
        return $this->data['unitPrice'];
    }

    public function getTotalPrice(): float
    {
        \assert(is_float($this->data['totalPrice']) || is_int($this->data['totalPrice']));
        return $this->data['totalPrice'];
    }

    public function getQuantity(): int
    {
        \assert(is_int($this->data['quantity']));
        return $this->data['quantity'];
    }

    /**
     * @return Collection<CalculatedTax>
     */
    public function getCalculatedTaxes(): Collection
    {
        \assert(is_array($this->data['calculatedTaxes']));

        return new Collection(
            \array_map(
                static fn (array $tax) => new CalculatedTax($tax),
                $this->data['calculatedTaxes']
            )
        );
    }

    /**
     * @return Collection<TaxRule>
     */
    public function getTaxRules(): Collection
    {
        \assert(is_array($this->data['taxRules']));

        return new Collection(
            \array_map(
                static fn (array $rule) => new TaxRule($rule),
                $this->data['taxRules']
            )
        );
    }

    /**
     * @param Collection<CalculatedPrice> $prices
     */
    public static function sum(Collection $prices): CalculatedPrice
    {
        /** @var array<array<CalculatedTax>> $allTaxes */
        $allTaxes = $prices->map(static fn (CalculatedPrice $price) => $price->getCalculatedTaxes()->all());

        $taxSum = CalculatedTax::sum(new Collection(array_merge(...$allTaxes)));

        $rules = [];

        foreach ($prices as $price) {
            $rules = array_merge($rules, $price->getTaxRules()->jsonSerialize());
        }

        return new CalculatedPrice([
            'unitPrice' => \array_sum($prices->map(static fn (CalculatedPrice $price): float => $price->getUnitPrice())),
            'totalPrice' => \array_sum($prices->map(static fn (CalculatedPrice $price): float => $price->getTotalPrice())),
            'quantity' => 1,
            'calculatedTaxes' => $taxSum->map(static fn (CalculatedTax $tax) => $tax->toArray()),
            'taxRules' => $rules,
        ]);
    }
}
