<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class OrderDelivery extends ArrayStruct
{
    use CustomFieldsAware;

    /**
     * @return array<string>
     */
    public function getTrackingCodes(): array
    {
        \assert(\is_array($this->data['trackingCodes']));
        return $this->data['trackingCodes'];
    }

    public function getShippingCosts(): CalculatedPrice
    {
        \assert(\is_array($this->data['shippingCosts']));
        return new CalculatedPrice($this->data['shippingCosts']);
    }

    public function getShippingOrderAddress(): Address
    {
        \assert(\is_array($this->data['shippingOrderAddress']));
        return new Address($this->data['shippingOrderAddress']);
    }

    public function getStateMachineState(): StateMachineState
    {
        \assert(\is_array($this->data['stateMachineState']));
        return new StateMachineState($this->data['stateMachineState']);
    }

    public function getShippingDateEarliest(): \DateTimeInterface
    {
        \assert(\is_string($this->data['shippingDateEarliest']));
        return new \DateTimeImmutable($this->data['shippingDateEarliest']);
    }

    public function getShippingDateLatest(): \DateTimeInterface
    {
        \assert(\is_string($this->data['shippingDateLatest']));
        return new \DateTimeImmutable($this->data['shippingDateLatest']);
    }
}
