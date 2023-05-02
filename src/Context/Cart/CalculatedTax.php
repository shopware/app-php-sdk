<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

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
}
