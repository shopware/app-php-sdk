<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class PaymentMethod extends ArrayStruct
{
    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getName(): string
    {
        \assert(is_string($this->data['name']));
        return $this->data['name'];
    }

    /**
     * @since Shopware v6.7.0.0
     */
    public function getTechnicalName(): string
    {
        \assert(is_string($this->data['technicalName']) || is_null($this->data['technicalName']));
        return $this->data['technicalName'] ?? '';
    }

    public function getDescription(): string
    {
        \assert(is_string($this->data['description']));
        return $this->data['description'];
    }

    public function isActive(): bool
    {
        \assert(is_bool($this->data['active']));
        return $this->data['active'];
    }

    public function isAfterOrderEnabled(): bool
    {
        \assert(is_bool($this->data['afterOrderEnabled']));
        return $this->data['afterOrderEnabled'];
    }

    public function getAvailabilityRuleId(): ?string
    {
        \assert(is_string($this->data['availabilityRuleId']) || is_null($this->data['availabilityRuleId']));
        return $this->data['availabilityRuleId'];
    }

    public function isSynchronous(): bool
    {
        \assert(is_bool($this->data['synchronous']));
        return $this->data['synchronous'];
    }

    public function isAsynchronous(): bool
    {
        \assert(is_bool($this->data['asynchronous']));
        return $this->data['asynchronous'];
    }

    public function isPrepared(): bool
    {
        \assert(is_bool($this->data['prepared']));
        return $this->data['prepared'];
    }

    public function isRefundable(): bool
    {
        \assert(is_bool($this->data['refundable']));
        return $this->data['refundable'];
    }
}
