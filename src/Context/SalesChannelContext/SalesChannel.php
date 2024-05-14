<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Framework\Collection;

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
     * @return Collection<SalesChannelDomain>
     */
    public function getDomains(): Collection
    {
        \assert(is_array($this->data['domains']));

        return new Collection(\array_map(static function (array $domain): SalesChannelDomain {
            return new SalesChannelDomain($domain);
        }, $this->data['domains']));
    }
}
