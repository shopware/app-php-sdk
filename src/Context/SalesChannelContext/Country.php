<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class Country extends ArrayStruct
{
    public function getName(): string
    {
        \assert(is_string($this->data['name']));
        return $this->data['name'];
    }

    public function getIso(): string
    {
        \assert(is_string($this->data['iso']));
        return $this->data['iso'];
    }

    public function getIso3(): string
    {
        \assert(is_string($this->data['iso3']));
        return $this->data['iso3'];
    }

    public function getCustomerTax(): TaxInfo
    {
        \assert(is_array($this->data['customerTax']));
        return new TaxInfo($this->data['customerTax']);
    }

    public function getCompanyTax(): TaxInfo
    {
        \assert(is_array($this->data['companyTax']));
        return new TaxInfo($this->data['companyTax']);
    }
}
