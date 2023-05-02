<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class CartPrice extends ArrayStruct
{
    public function getNetPrice(): float
    {
        \assert(is_float($this->data['netPrice']));
        return $this->data['netPrice'];
    }

    public function getTotalPrice(): float
    {
        \assert(is_float($this->data['totalPrice']));
        return $this->data['totalPrice'];
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

    public function getTaxStatus(): string
    {
        \assert(is_string($this->data['taxStatus']));
        return $this->data['taxStatus'];
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

    public function getPositionPrice(): float
    {
        \assert(is_float($this->data['positionPrice']));
        return $this->data['positionPrice'];
    }

    public function getRawTotal(): float
    {
        \assert(is_float($this->data['rawTotal']));
        return $this->data['rawTotal'];
    }
}
