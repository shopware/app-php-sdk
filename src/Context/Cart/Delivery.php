<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;
use Shopware\App\SDK\Framework\Collection;

class Delivery extends ArrayStruct
{
    /**
     * @return Collection<DeliveryPosition>
     */
    public function getPositions(): Collection
    {
        \assert(is_array($this->data['positions']));

        return new Collection(\array_map(static function (array $position) {
            return new DeliveryPosition($position);
        }, $this->data['positions']));
    }

    public function getLocation(): ShippingLocation
    {
        \assert(is_array($this->data['location']));
        return new ShippingLocation($this->data['location']);
    }

    public function getShippingMethod(): ShippingMethod
    {
        \assert(is_array($this->data['shippingMethod']));
        return new ShippingMethod($this->data['shippingMethod']);
    }

    public function getDeliveryDate(): DeliveryDate
    {
        \assert(is_array($this->data['deliveryDate']));
        return new DeliveryDate($this->data['deliveryDate']);
    }

    public function getShippingCosts(): CalculatedPrice
    {
        \assert(is_array($this->data['shippingCosts']));
        return new CalculatedPrice($this->data['shippingCosts']);
    }
}
