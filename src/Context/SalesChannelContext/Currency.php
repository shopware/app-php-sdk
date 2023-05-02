<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class Currency extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getIsoCode(): string
    {
        \assert(is_string($this->data['isoCode']));
        return $this->data['isoCode'];
    }

    public function getFactor(): float
    {
        \assert(is_float($this->data['factor']) || is_int($this->data['factor']));
        return $this->data['factor'];
    }

    public function getSymbol(): string
    {
        \assert(is_string($this->data['symbol']));
        return $this->data['symbol'];
    }

    public function getShortName(): string
    {
        \assert(is_string($this->data['shortName']));
        return $this->data['shortName'];
    }

    public function getName(): string
    {
        \assert(is_string($this->data['name']));
        return $this->data['name'];
    }

    public function getItemRounding(): RoundingConfig
    {
        \assert(is_array($this->data['itemRounding']));
        return new RoundingConfig($this->data['itemRounding']);
    }

    public function getTotalRounding(): RoundingConfig
    {
        \assert(is_array($this->data['totalRounding']));
        return new RoundingConfig($this->data['totalRounding']);
    }

    public function getTaxFreeFrom(): float
    {
        \assert(is_float($this->data['taxFreeFrom']) || is_int($this->data['taxFreeFrom']));
        return (float) $this->data['taxFreeFrom'];
    }
}
