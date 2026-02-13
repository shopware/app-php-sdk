<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Payment;

use Shopware\App\SDK\Context\ArrayStruct;

/**
 * Use `toArray` to access the data instead of getters.
 * The structure of the returned array from `toArray` is not defined and can vary between implementations.
 */
class RecurringData extends ArrayStruct
{
    /**
     * @deprecated tag:v6.0.0 - will be removed like in Shopware platform (with 6.8.0.0)
     */
    public function getSubscriptionId(): string
    {
        \assert(\is_string($this->data['subscriptionId']));
        return $this->data['subscriptionId'];
    }

    /**
     * @deprecated tag:v6.0.0 - will be removed like in Shopware platform (with 6.8.0.0)
     */
    public function getNextSchedule(): \DateTimeInterface
    {
        \assert(\is_string($this->data['nextSchedule']));
        return new \DateTime($this->data['nextSchedule']);
    }
}
