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

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function withClientKey(string $apiKey): ShopInterface
    {
        $this->clientId = $apiKey;

        return $this;
    }

    public function withClientSecret(string $secretKey): ShopInterface
    {
        $this->clientSecret = $secretKey;

        return $this;
    }

    public function withShopUrl(string $url): ShopInterface
    {
        $this->shopUrl = $url;

        return $this;
    }
}
