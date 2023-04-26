<?php

declare(strict_types=1);

namespace Shopware\AppSDK\Shop;

interface ShopRepositoryInterface
{
    public function createShopFromArray(string $shopId, string $shopUrl, string $shopSecret): ShopInterface;

    public function createShop(ShopInterface $shop): void;

    public function getShopFromId(string $shopId): ShopInterface|null;

    public function updateShop(ShopInterface $shop): void;

    public function deleteShop(ShopInterface $shop): void;
}
