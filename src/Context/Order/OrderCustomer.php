<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class OrderCustomer extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getEmail(): string
    {
        \assert(is_string($this->data['email']));
        return $this->data['email'];
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

    public function getTitle(): ?string
    {
        \assert(is_string($this->data['title']) || is_null($this->data['title']));
        return $this->data['title'];
    }

    /**
     * @return string[]
     */
    public function getVatIds(): array
    {
        \assert(is_array($this->data['vatIds']));
        return $this->data['vatIds'];
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

    public function getSalutation(): ?Salutation
    {
        \assert(is_array($this->data['salutation']) || is_null($this->data['salutation']));

        if ($this->data['salutation'] === null) {
            return null;
        }

        return new Salutation($this->data['salutation']);
    }

    public function getRemoteAddress(): string
    {
        \assert(is_string($this->data['remoteAddress']));
        return $this->data['remoteAddress'];
    }

    public function getCustomer(): Customer
    {
        \assert(\is_array($this->data['customer']));
        return new Customer($this->data['customer']);
    }
}
