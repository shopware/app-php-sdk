<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Framework\Collection;

class CalculatedTax extends ArrayStruct
{
    public function getTaxRate(): float
    {
        \assert(is_float($this->data['taxRate']) || is_int($this->data['taxRate']));
        return $this->data['taxRate'];
    }

    public function getPrice(): float
    {
        \assert(is_float($this->data['price']) || is_int($this->data['price']));
        return $this->data['price'];
    }

    public function getTax(): float
    {
        \assert(is_float($this->data['tax']) || is_int($this->data['tax']));
        return $this->data['tax'];
    }

    /**
     * @param Collection<CalculatedTax> $calculatedTaxes
     * @return Collection<CalculatedTax>
     */
    public static function sum(Collection $calculatedTaxes): Collection
    {
        $new = [];

        foreach ($calculatedTaxes as $calculatedTax) {
            $exists = isset($new[$calculatedTax->getTaxRate()]);
            if (!$exists) {
                $new[$calculatedTax->getTaxRate()] = $calculatedTax;

                continue;
            }

            $new[$calculatedTax->getTaxRate()] = new CalculatedTax([
                'taxRate' => $calculatedTax->getTaxRate(),
                'price' => $new[$calculatedTax->getTaxRate()]->getPrice() + $calculatedTax->getPrice(),
                'tax' => $new[$calculatedTax->getTaxRate()]->getTax() + $calculatedTax->getTax(),
            ]);
        }

        return new Collection($new);
    }
}
