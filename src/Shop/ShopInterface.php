<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Shop;

interface ShopInterface
{
    public function getShopId(): string;

    public function getShopUrl(): string;

    public function getShopSecret(): string;

    public function getShopClientId(): ?string;

    public function getShopClientSecret(): ?string;

    public function withShopApiCredentials(string $clientId, string $clientSecret): ShopInterface;

    public function withShopUrl(string $url): ShopInterface;
}
