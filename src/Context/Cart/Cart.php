<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Cart;

use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Framework\Collection;

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
     * @return Collection<LineItem>
     */
    public function getLineItems(): Collection
    {
        \assert(\is_array($this->data['lineItems']));

        return new Collection(\array_map(
            static fn (array $lineItem) => new LineItem($lineItem),
            $this->data['lineItems']
        ));
    }

    /**
     * @return Collection<Delivery>
     */
    public function getDeliveries(): Collection
    {
        \assert(\is_array($this->data['deliveries']));

        return new Collection(\array_map(
            static fn (array $delivery) => new Delivery($delivery),
            $this->data['deliveries']
        ));
    }

    /**
     * @return Collection<CartTransaction>
     */
    public function getTransactions(): Collection
    {
        \assert(\is_array($this->data['transactions']));

        return new Collection(\array_map(
            static fn (array $transaction) => new CartTransaction($transaction),
            $this->data['transactions']
        ));
    }

    public function getPrice(): CartPrice
    {
        \assert(is_array($this->data['price']));
        return new CartPrice($this->data['price']);
    }
}
