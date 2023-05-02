<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class TaxInfo extends ArrayStruct
{
    public function isEnabled(): bool
    {
        \assert(is_bool($this->data['enabled']));
        return $this->data['enabled'];
    }

    public function getCurrencyId(): string
    {
        \assert(is_string($this->data['currencyId']));
        return $this->data['currencyId'];
    }

    public function getAmount(): float
    {
        \assert(is_float($this->data['amount']) || is_int($this->data['amount']));
        return $this->data['amount'];
    }
}
