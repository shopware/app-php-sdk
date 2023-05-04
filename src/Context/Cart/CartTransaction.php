<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class CartTransaction extends ArrayStruct
{
    public function getPaymentMethodId(): string
    {
        \assert(is_string($this->data['paymentMethodId']));
        return $this->data['paymentMethodId'];
    }

    public function getAmount(): CalculatedPrice
    {
        \assert(is_array($this->data['amount']));
        return new CalculatedPrice($this->data['amount']);
    }
}
