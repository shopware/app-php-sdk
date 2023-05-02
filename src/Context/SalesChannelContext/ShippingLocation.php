<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class ShippingLocation extends ArrayStruct
{
    public function getCountry(): Country
    {
        \assert(is_array($this->data['country']));
        return new Country($this->data['country']);
    }

    public function getCountryState(): ?CountryState
    {
        if (is_null($this->data['countryState'])) {
            return null;
        }

        \assert(is_array($this->data['countryState']));
        return new CountryState($this->data['countryState']);
    }

    public function getAddress(): Address
    {
        \assert(is_array($this->data['address']));
        return new Address($this->data['address']);
    }
}
