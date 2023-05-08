<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class DeliveryPosition extends ArrayStruct
{
    public function getIdentifier(): string
    {
        \assert(is_string($this->data['identifier']));
        return $this->data['identifier'];
    }

    public function getLineItem(): LineItem
    {
        \assert(is_array($this->data['lineItem']));
        return new LineItem($this->data['lineItem']);
    }

    public function getQuantity(): int
    {
        \assert(is_int($this->data['quantity']));
        return $this->data['quantity'];
    }

    public function getDeliveryDate(): DeliveryDate
    {
        \assert(is_array($this->data['deliveryDate']));
        return new DeliveryDate($this->data['deliveryDate']);
    }

    public function getPrice(): CalculatedPrice
    {
        \assert(is_array($this->data['price']));
        return new CalculatedPrice($this->data['price']);
    }
}
