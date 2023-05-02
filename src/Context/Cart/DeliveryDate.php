<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class DeliveryDate extends ArrayStruct
{
    public function getEarliest(): \DateTimeInterface
    {
        \assert(is_string($this->data['earliest']));
        return new \DateTimeImmutable($this->data['earliest']);
    }

    public function getLatest(): \DateTimeInterface
    {
        \assert(is_string($this->data['latest']));
        return new \DateTimeImmutable($this->data['latest']);
    }
}
