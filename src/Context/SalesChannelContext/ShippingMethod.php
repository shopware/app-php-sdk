<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class ShippingMethod extends ArrayStruct
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

    public function getTaxType(): string
    {
        \assert(is_string($this->data['taxType']));
        return $this->data['taxType'];
    }
}
