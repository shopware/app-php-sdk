<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class Address extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getSalutation(): ?Salutation
    {
        if (!isset($this->data['salutation'])) {
            return null;
        }

        \assert(is_array($this->data['salutation']));
        return new Salutation($this->data['salutation']);
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

    public function getStreet(): string
    {
        \assert(is_string($this->data['street']));
        return $this->data['street'];
    }

    public function getZipCode(): string
    {
        \assert(is_string($this->data['zipcode']));
        return $this->data['zipcode'];
    }

    public function getCity(): string
    {
        \assert(is_string($this->data['city']));
        return $this->data['city'];
    }

    public function getCompany(): ?string
    {
        if (!isset($this->data['company'])) {
            return null;
        }

        \assert(is_string($this->data['company']));
        return $this->data['company'];
    }

    public function getDepartment(): ?string
    {
        if (!isset($this->data['department'])) {
            return null;
        }

        \assert(is_string($this->data['department']));
        return $this->data['department'];
    }

    public function getTitle(): ?string
    {
        if (!isset($this->data['title'])) {
            return null;
        }

        \assert(is_string($this->data['title']));
        return $this->data['title'];
    }

    public function getCountry(): Country
    {
        \assert(is_array($this->data['country']));
        return new Country($this->data['country']);
    }

    public function getCountryState(): ?CountryState
    {
        if (!isset($this->data['countryState'])) {
            return null;
        }

        \assert(is_array($this->data['countryState']));
        return new CountryState($this->data['countryState']);
    }

    public function getPhoneNumber(): ?string
    {
        if (!isset($this->data['phoneNumber'])) {
            return null;
        }

        \assert(is_string($this->data['phoneNumber']));
        return $this->data['phoneNumber'];
    }

    public function getAdditionalAddressLine1(): ?string
    {
        if (!isset($this->data['additionalAddressLine1'])) {
            return null;
        }

        \assert(is_string($this->data['additionalAddressLine1']));
        return $this->data['additionalAddressLine1'];
    }

    public function getAdditionalAddressLine2(): ?string
    {
        if (!isset($this->data['additionalAddressLine2'])) {
            return null;
        }

        \assert(is_string($this->data['additionalAddressLine2']));
        return $this->data['additionalAddressLine2'];
    }
}
