<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;

class Cart extends ArrayStruct
{
    public function getToken(): string
    {
        \assert(is_string($this->data['token']));
        return $this->data['token'];
    }

    public function getCustomerComment(): ?string
    {
        \assert(is_string($this->data['customerComment']) || is_null($this->data['customerComment']));
        return $this->data['customerComment'];
    }

    public function getAffiliateCode(): ?string
    {
        \assert(is_string($this->data['affiliateCode']) || is_null($this->data['affiliateCode']));
        return $this->data['affiliateCode'];
    }

    public function getCampaignCode(): ?string
    {
        \assert(is_string($this->data['campaignCode']) || is_null($this->data['campaignCode']));
        return $this->data['campaignCode'];
    }

    /**
     * @return array<LineItem>
     */
    public function getLineItems(): array
    {
        \assert(is_array($this->data['lineItems']));
        return array_map(
            fn (array $lineItem) => new LineItem($lineItem),
            $this->data['lineItems']
        );
    }

    /**
     * @return array<Delivery>
     */
    public function getDeliveries(): array
    {
        \assert(is_array($this->data['deliveries']));
        return array_map(
            fn (array $delivery) => new Delivery($delivery),
            $this->data['deliveries']
        );
    }

    /**
     * @return array<CartTransaction>
     */
    public function getTransactions(): array
    {
        \assert(is_array($this->data['transactions']));
        return array_map(
            fn (array $transaction) => new CartTransaction($transaction),
            $this->data['transactions']
        );
    }

    public function getPrice(): CartPrice
    {
        \assert(is_array($this->data['price']));
        return new CartPrice($this->data['price']);
    }
}
