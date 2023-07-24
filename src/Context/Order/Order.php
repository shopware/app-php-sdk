<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Order;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CartPrice;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\LineItem;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;
use Shopware\App\SDK\Context\Trait\CustomFieldsAware;

class Order extends ArrayStruct
{
    use CustomFieldsAware;

    public function getId(): string
    {
        \assert(\is_string($this->data['id']));
        return $this->data['id'];
    }

    public function getOrderNumber(): string
    {
        \assert(\is_string($this->data['orderNumber']));
        return $this->data['orderNumber'];
    }

    public function getCurrencyFactor(): float
    {
        \assert(\is_float($this->data['currencyFactor']) || is_int($this->data['currencyFactor']));
        return $this->data['currencyFactor'];
    }

    public function getOrderDate(): \DateTimeInterface
    {
        \assert(is_string($this->data['orderDateTime']));
        return new \DateTimeImmutable($this->data['orderDateTime']);
    }

    public function getPrice(): CartPrice
    {
        \assert(\is_array($this->data['price']));
        return new CartPrice($this->data['price']);
    }

    public function getAmountTotal(): float
    {
        \assert(\is_float($this->data['amountTotal']) || is_int($this->data['amountTotal']));
        return $this->data['amountTotal'];
    }

    public function getAmountNet(): float
    {
        \assert(\is_float($this->data['amountNet']) || is_int($this->data['amountNet']));
        return $this->data['amountNet'];
    }

    public function getPositionPrice(): float
    {
        \assert(\is_float($this->data['positionPrice']) || is_int($this->data['positionPrice']));
        return $this->data['positionPrice'];
    }

    public function getTaxStatus(): string
    {
        \assert(\is_string($this->data['taxStatus']));
        return $this->data['taxStatus'];
    }

    public function getShippingTotal(): float
    {
        \assert(\is_float($this->data['shippingTotal']) || is_int($this->data['shippingTotal']));
        return $this->data['shippingTotal'];
    }

    public function getShippingCosts(): CalculatedPrice
    {
        \assert(\is_array($this->data['shippingCosts']));
        return new CalculatedPrice($this->data['shippingCosts']);
    }

    public function getOrderCustomer(): Customer
    {
        \assert(\is_array($this->data['orderCustomer']));
        return new Customer($this->data['orderCustomer']);
    }

    public function getCurrency(): Currency
    {
        \assert(\is_array($this->data['currency']));
        return new Currency($this->data['currency']);
    }

    public function getBillingAddress(): Address
    {
        \assert(\is_array($this->data['billingAddress']));
        return new Address($this->data['billingAddress']);
    }

    /**
     * @return array<LineItem>
     */
    public function getLineItems(): array
    {
        \assert(\is_array($this->data['lineItems']));
        return array_map(static function (array $lineItem): LineItem {
            return new LineItem($lineItem);
        }, $this->data['lineItems']);
    }

    public function getItemRounding(): RoundingConfig
    {
        \assert(\is_array($this->data['itemRounding']));
        return new RoundingConfig($this->data['itemRounding']);
    }

    public function getTotalRounding(): RoundingConfig
    {
        \assert(\is_array($this->data['totalRounding']));
        return new RoundingConfig($this->data['totalRounding']);
    }

    public function getDeepLinkCode(): string
    {
        \assert(\is_string($this->data['deepLinkCode']));
        return $this->data['deepLinkCode'];
    }

    public function getSalesChannelId(): string
    {
        \assert(\is_string($this->data['salesChannelId']));
        return $this->data['salesChannelId'];
    }

    /**
     * @return array<OrderDelivery>
     */
    public function getDeliveries(): array
    {
        \assert(\is_array($this->data['deliveries']));
        return array_map(static function (array $delivery): OrderDelivery {
            return new OrderDelivery($delivery);
        }, $this->data['deliveries']);
    }

    /**
     * @return array<OrderTransaction>
     */
    public function getTransactions(): array
    {
        \assert(\is_array($this->data['transactions']));
        return array_map(static function (array $transaction): OrderTransaction {
            return new OrderTransaction($transaction);
        }, $this->data['transactions']);
    }
}
