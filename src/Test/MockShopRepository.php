<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Test;

use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;

/**
 * @implements ShopRepositoryInterface<MockShop>
 */
class MockShopRepository implements ShopRepositoryInterface
{
    /**
     * @var array<string, MockShop>
     */
    public array $shops = [];

    public function createShopStruct(string $shopId, string $shopUrl, string $shopSecret): ShopInterface
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

    public function deleteShop(string $shopId): void
    {
        unset($this->shops[$shopId]);
    }
}
