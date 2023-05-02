<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class CountryState extends ArrayStruct
{
    use CustomFieldsAware;

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

    public function getShortCode(): string
    {
        \assert(is_string($this->data['shortCode']));
        return $this->data['shortCode'];
    }

    public function getPosition(): int
    {
        \assert(is_int($this->data['position']));
        return $this->data['position'];
    }
}
