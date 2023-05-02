<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class Customer extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getFirstName(): string
    {
        \assert(is_string($this->data['firstName']));
        return $this->data['firstName'];
    }

    public function getLastName(): string
    {
        \assert(is_string($this->data['lastName']));
        return $this->data['lastName'];
    }

    public function getCompany(): ?string
    {
        \assert(is_string($this->data['company']) || is_null($this->data['company']));
        return $this->data['company'];
    }

    public function getCustomerNumber(): string
    {
        \assert(is_string($this->data['customerNumber']));
        return $this->data['customerNumber'];
    }

    public function getTitle(): ?string
    {
        \assert(is_string($this->data['title']) || is_null($this->data['title']));
        return $this->data['title'];
    }

    public function isActive(): bool
    {
        \assert(is_bool($this->data['active']));
        return $this->data['active'];
    }

    public function isGuest(): bool
    {
        \assert(is_bool($this->data['guest']));
        return $this->data['guest'];
    }

    public function getAccountType(): string
    {
        \assert(is_string($this->data['accountType']));
        return $this->data['accountType'];
    }

    /**
     * @return array<string>
     */
    public function getVatIds(): array
    {
        \assert(is_array($this->data['vatIds']) || is_null($this->data['vatIds']));
        return $this->data['vatIds'] ?? [];
    }

    public function getRemoteAddress(): string
    {
        \assert(is_string($this->data['remoteAddress']));
        return $this->data['remoteAddress'];
    }

    public function getSalutation(): ?Salutation
    {
        \assert(is_array($this->data['salutation']) || is_null($this->data['salutation']));

        if ($this->data['salutation'] === null) {
            return null;
        }

        return new Salutation($this->data['salutation']);
    }

    public function getDefaultPaymentMethod(): PaymentMethod
    {
        \assert(is_array($this->data['defaultPaymentMethod']));
        return new PaymentMethod($this->data['defaultPaymentMethod']);
    }

    public function getDefaultBillingAddress(): Address
    {
        \assert(is_array($this->data['defaultBillingAddress']));
        return new Address($this->data['defaultBillingAddress']);
    }

    public function getDefaultShippingAddress(): Address
    {
        \assert(is_array($this->data['defaultShippingAddress']));
        return new Address($this->data['defaultShippingAddress']);
    }

    public function getActiveBillingAddress(): Address
    {
        \assert(is_array($this->data['activeBillingAddress']));
        return new Address($this->data['activeBillingAddress']);
    }

    public function getActiveShippingAddress(): Address
    {
        \assert(is_array($this->data['activeShippingAddress']));
        return new Address($this->data['activeShippingAddress']);
    }
}
