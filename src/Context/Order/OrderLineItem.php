<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\Cart\LineItem;

class OrderLineItem extends LineItem
{
    public function getParentId(): ?string
    {
        \assert(is_string($this->data['parentId']) || is_null($this->data['parentId']));
        return $this->data['parentId'];
    }

    public function getPosition(): int
    {
        \assert(is_int($this->data['position']));
        return $this->data['position'];
    }
}
