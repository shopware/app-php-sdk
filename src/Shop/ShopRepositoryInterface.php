<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Shop;

/**
 * @template T of ShopInterface
 */
interface ShopRepositoryInterface
{
    /**
     * @return T
     */
    public function createShopStruct(string $shopId, string $shopUrl, string $shopSecret): ShopInterface;

    /**
     * @param T $shop
     */
    public function createShop(ShopInterface $shop): void;

    /**
     * @return T|null
     */
    public function getShopFromId(string $shopId): ShopInterface|null;

    /**
     * @param T $shop
     */
    public function updateShop(ShopInterface $shop): void;

    public function deleteShop(string $shopId): void;
}
