<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class OrderTransaction extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(\is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getAmount(): CalculatedPrice
    {
        \assert(\is_array($this->data['amount']));
        return new CalculatedPrice($this->data['amount']);
    }

    public function getPaymentMethod(): PaymentMethod
    {
        \assert(\is_array($this->data['paymentMethod']));
        return new PaymentMethod($this->data['paymentMethod']);
    }

    public function getStateMachineState(): StateMachineState
    {
        \assert(\is_array($this->data['stateMachineState']));
        return new StateMachineState($this->data['stateMachineState']);
    }

    public function getOrder(): Order
    {
        \assert(\is_array($this->data['order']));
        return new Order($this->data['order']);
    }

    /**
     * @return array<mixed>
     */
    public function getValidationData(): array
    {
        \assert(\is_array($this->data['validationData']));
        return $this->data['validationData'];
    }
}
