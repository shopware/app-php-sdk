<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;

class Delivery extends ArrayStruct
{
    /**
     * @return array<DeliveryPosition>
     */
    public function getPositions(): array
    {
        \assert(is_array($this->data['positions']));

        return array_map(function (array $position) {
            return new DeliveryPosition($position);
        }, $this->data['positions']);
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
}
