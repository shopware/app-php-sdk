<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ArrayStruct;

class RecurringData extends ArrayStruct
{
    public function getSubscriptionId(): string
    {
        \assert(\is_string($this->data['subscriptionId']));
        return $this->data['subscriptionId'];
    }

    public function getNextSchedule(): \DateTimeInterface
    {
        \assert(\is_string($this->data['nextSchedule']));
        return new \DateTime($this->data['nextSchedule']);
    }
}
