<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

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
     * @return array<CalculatedTax>
     */
    public function getCalculatedTaxes(): array
    {
        \assert(is_array($this->data['calculatedTaxes']));
        return array_map(function (array $calculatedTax): CalculatedTax {
            return new CalculatedTax($calculatedTax);
        }, $this->data['calculatedTaxes']);
    }

    /**
     * @return array<TaxRule>
     */
    public function getTaxRules(): array
    {
        \assert(is_array($this->data['taxRules']));
        return array_map(function (array $taxRule): TaxRule {
            return new TaxRule($taxRule);
        }, $this->data['taxRules']);
    }
}
