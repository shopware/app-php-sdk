<?php

declare(strict_types=1);

use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Test\MockShop;

/**
 * This is a simple example implementation of a ShopRepositoryInterface.
 * Do not use this in production.
 */
class FileShopRepository implements ShopRepositoryInterface
{
    private function getPath(string $shopId): string
    {
        if (!file_exists(__DIR__ . '/shops/')) {
            mkdir(__DIR__ . '/shops/');
        }

        return __DIR__ . '/shops/' . $shopId . '.json';
    }

    public function createShopStruct(string $shopId, string $shopUrl, string $shopSecret): ShopInterface
    {
        return new MockShop($shopId, $shopUrl, $shopSecret);
    }

    public function createShop(ShopInterface $shop): void
    {
        file_put_contents($this->getPath($shop->getShopId()), serialize($shop));
    }

    public function getShopFromId(string $shopId): ShopInterface|null
    {
        $path = $this->getPath($shopId);

        if (!file_exists($path)) {
            return null;
        }

        return unserialize(file_get_contents($path));
    }

    public function updateShop(ShopInterface $shop): void
    {
        $this->createShop($shop);
    }

    public function deleteShop(string $shopId): void
    {
        $path = $this->getPath($shopId);

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
