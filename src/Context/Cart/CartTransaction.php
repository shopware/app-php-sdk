<?php

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class CartTransaction extends ArrayStruct
{
    public function getPaymentMethodId(): string
    {
        \assert(is_string($this->data['paymentMethodId']));
        return $this->data['paymentMethodId'];
    }

    public function getAmount(): LineItemPrice
    {
        \assert(is_array($this->data['amount']));
        return new LineItemPrice($this->data['amount']);
    }
}