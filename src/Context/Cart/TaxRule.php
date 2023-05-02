<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class TaxRule extends ArrayStruct
{
    public function getTaxRate(): float
    {
        \assert(is_float($this->data['taxRate']) || is_int($this->data['taxRate']));
        return $this->data['taxRate'];
    }

    public function getPercentage(): float
    {
        \assert(is_float($this->data['percentage']) || is_int($this->data['percentage']));
        return $this->data['percentage'];
    }
}
