<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class SalesChannel extends ArrayStruct
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

    public function getAccessKey(): string
    {
        \assert(is_string($this->data['accessKey']));
        return $this->data['accessKey'];
    }

    public function getTaxCalculationType(): string
    {
        \assert(is_string($this->data['taxCalculationType']));
        return $this->data['taxCalculationType'];
    }

    public function getCurrency(): Currency
    {
        \assert(is_array($this->data['currency']));
        return new Currency($this->data['currency']);
    }

    /**
     * @return array<SalesChannelDomain>
     */
    public function getDomains(): array
    {
        \assert(is_array($this->data['domains']));
        return array_map(function (array $domain): SalesChannelDomain {
            return new SalesChannelDomain($domain);
        }, $this->data['domains']);
    }
}
