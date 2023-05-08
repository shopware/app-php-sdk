<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class RefundTransactionCapture extends ArrayStruct
{
    use CustomFieldsAware;

    public function getExternalReference(): ?string
    {
        \assert(is_string($this->data['externalReference']) || $this->data['externalReference'] === null);
        return $this->data['externalReference'];
    }

    public function getAmount(): CalculatedPrice
    {
        \assert(is_array($this->data['amount']));
        return new CalculatedPrice($this->data['amount']);
    }

    public function getTransaction(): OrderTransaction
    {
        \assert(is_array($this->data['transaction']));
        return new OrderTransaction($this->data['transaction']);
    }
}
