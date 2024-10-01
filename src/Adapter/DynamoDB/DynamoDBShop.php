<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Adapter\DynamoDB;

use Shopware\App\SDK\Shop\ShopInterface;

class DynamoDBShop implements ShopInterface
{
    public function __construct(public string $shopId, public string $shopUrl, public string $shopSecret, public ?string $shopClientId = null, public ?string $shopClientSecret = null, public bool $active = false)
    {
    }

    public function isShopActive(): bool
    {
        return $this->active;
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
        return $this->shopClientId;
    }

    public function getShopClientSecret(): ?string
    {
        return $this->shopClientSecret;
    }

    public function setShopApiCredentials(string $clientId, string $clientSecret): ShopInterface
    {
        $this->shopClientId = $clientId;
        $this->shopClientSecret = $clientSecret;

        return $this;
    }

    public function setShopUrl(string $url): ShopInterface
    {
        $this->shopUrl = $url;

        return $this;
    }

    public function setShopActive(bool $active): ShopInterface
    {
        $this->active = $active;

        return $this;
    }
}
