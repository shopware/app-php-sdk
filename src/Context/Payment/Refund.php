<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\StateMachineState;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class Refund extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getReason(): ?string
    {
        \assert(is_string($this->data['reason']) || $this->data['reason'] === null);
        return $this->data['reason'];
    }

    public function getAmount(): CalculatedPrice
    {
        \assert(is_array($this->data['amount']));
        return new CalculatedPrice($this->data['amount']);
    }

    public function getStateMachineState(): StateMachineState
    {
        \assert(is_array($this->data['stateMachineState']));
        return new StateMachineState($this->data['stateMachineState']);
    }

    public function getTransactionCapture(): RefundTransactionCapture
    {
        \assert(is_array($this->data['transactionCapture']));
        return new RefundTransactionCapture($this->data['transactionCapture']);
    }
}
