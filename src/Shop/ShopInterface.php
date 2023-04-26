<?php

declare(strict_types=1);

namespace Shopware\AppSDK\Shop;

interface ShopInterface
{
    public function getShopId(): string;

    public function getShopUrl(): string;

    public function getShopSecret(): string;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;

    public function withClientKey(string $apiKey): ShopInterface;

    public function withClientSecret(string $secretKey): ShopInterface;
}
