<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Test;

use Shopware\App\SDK\Shop\ShopInterface;

class MockShop implements ShopInterface
{
    public function __construct(
        private string $shopId,
        private string $shopUrl,
        private string $shopSecret,
        private ?string $clientId = null,
        private ?string $clientSecret = null
    ) {
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getShopUrl(): string
    {
        return $this->shopUrl;
    }

    public function getShopSecret(): string
    {
        return $this->shopSecret;
    }

    public function getShopClientId(): ?string
    {
        return $this->clientId;
    }

    public function getShopClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function withShopApiCredentials(string $clientId, string $clientSecret): ShopInterface
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function withShopUrl(string $url): ShopInterface
    {
        $this->shopUrl = $url;

        return $this;
    }
}
