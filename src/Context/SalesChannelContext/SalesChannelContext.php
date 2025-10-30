<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\SalesChannelContext;

use Shopware\App\SDK\Context\ArrayStruct;

class SalesChannelContext extends ArrayStruct
{
    /**
     * Current sales channel context token
     */
    public function getToken(): string
    {
        \assert(is_string($this->data['token']));
        return $this->data['token'];
    }

    public function getCurrencyId(): string
    {
        \assert(is_array($this->data['context']) && is_string($this->data['context']['currencyId']));
        return $this->data['context']['currencyId'];
    }

    /**
     * @return string
     */
    public function getTaxState(): string
    {
        \assert(is_array($this->data['context']) && is_string($this->data['context']['taxState']));
        return $this->data['context']['taxState'];
    }

    public function getRounding(): RoundingConfig
    {
        \assert(is_array($this->data['context']) && is_array($this->data['context']['rounding']));
        return new RoundingConfig($this->data['context']['rounding']);
    }

    public function getCurrency(): Currency
    {
        \assert(is_array($this->data['currency']));
        return new Currency($this->data['currency']);
    }

    public function getShippingMethod(): ShippingMethod
    {
        \assert(is_array($this->data['shippingMethod']));
        return new ShippingMethod($this->data['shippingMethod']);
    }

    public function getPaymentMethod(): PaymentMethod
    {
        \assert(is_array($this->data['paymentMethod']));
        return new PaymentMethod($this->data['paymentMethod']);
    }

    public function getSalesChannel(): SalesChannel
    {
        \assert(is_array($this->data['salesChannel']));
        return new SalesChannel($this->data['salesChannel']);
    }

    public function getCustomer(): ?Customer
    {
        \assert(is_array($this->data['customer']) || is_null($this->data['customer']));

        if (is_null($this->data['customer'])) {
            return null;
        }

        return new Customer($this->data['customer']);
    }

    public function getLanguageInfo(): LanguageInfo
    {
        \assert(\is_array($this->data['languageInfo']));
        return new LanguageInfo($this->data['languageInfo']);
    }
}
