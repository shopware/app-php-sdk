<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Framework\Collection;

class CartPrice extends ArrayStruct
{
    final public const TAX_STATE_GROSS = 'gross';
    final public const TAX_STATE_NET = 'net';
    final public const TAX_STATE_FREE = 'tax-free';

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
     * @return Collection<CalculatedTax>
     */
    public function getCalculatedTaxes(): Collection
    {
        \assert(is_array($this->data['calculatedTaxes']));

        return new Collection(\array_map(static function (array $calculatedTax): CalculatedTax {
            return new CalculatedTax($calculatedTax);
        }, $this->data['calculatedTaxes']));
    }

    public function getTaxStatus(): string
    {
        \assert(is_string($this->data['taxStatus']));
        return $this->data['taxStatus'];
    }

    /**
     * @return Collection<TaxRule>
     */
    public function getTaxRules(): Collection
    {
        \assert(\is_array($this->data['taxRules']));

        return new Collection(\array_map(static function (array $taxRule): TaxRule {
            return new TaxRule($taxRule);
        }, $this->data['taxRules']));
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
