<?php

namespace Shopware\AppSDK\Test;

use Shopware\AppSDK\Shop\ShopInterface;
use Shopware\AppSDK\Shop\ShopRepositoryInterface;

class MockShopRepository implements ShopRepositoryInterface
{
    /**
     * @var array<string, ShopInterface>
     */
    public array $shops = [];

    public function createShopFromArray(string $shopId, string $shopUrl, string $shopSecret): ShopInterface
    {
        return new MockShop($shopId, $shopUrl, $shopSecret);
    }

    public function createShop(ShopInterface $shop): void
    {
        $this->shops[$shop->getShopId()] = $shop;
    }

    public function getShopFromId(string $shopId): ShopInterface|null
    {
        return $this->shops[$shopId] ?? null;
    }

    public function updateShop(ShopInterface $shop): void
    {
        $this->shops[$shop->getShopId()] = $shop;
    }

    public function deleteShop(ShopInterface $shop): void
    {
        unset($this->shops[$shop->getShopId()]);
    }
}
