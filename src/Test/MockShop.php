<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Test;

use Shopware\App\SDK\Shop\ShopInterface;

class MockShop implements ShopInterface
{
    public function __construct(
        private readonly string $shopId,
        private string $shopUrl,
        private string $shopSecret,
        private bool $shopActive = false,
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private ?string $pendingShopSecret = null,
        private ?string $pendingShopUrl = null,
        private ?string $previousShopSecret = null,
        private ?\DateTimeImmutable $secretsRotatedAt = null,
        /**
         * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
         */
        private bool $hasVerifiedWithDoubleSignature = false,
        private bool $registrationConfirmed = false,
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

    public function getPreviousShopSecret(): ?string
    {
        return $this->previousShopSecret;
    }

    public function getPendingShopSecret(): ?string
    {
        return $this->pendingShopSecret;
    }

    public function getShopClientId(): ?string
    {
        return $this->clientId;
    }

    public function getShopClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getPendingShopUrl(): ?string
    {
        return $this->pendingShopUrl;
    }

    public function getSecretsRotatedAt(): ?\DateTimeImmutable
    {
        return $this->secretsRotatedAt;
    }

    public function isShopActive(): bool
    {
        return $this->shopActive;
    }

    public function setShopApiCredentials(string $clientId, string $clientSecret): ShopInterface
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function setShopSecret(string $secret): ShopInterface
    {
        $this->shopSecret = $secret;

        return $this;
    }

    public function setShopUrl(string $url): ShopInterface
    {
        $this->shopUrl = $url;

        return $this;
    }

    public function setShopActive(bool $active): ShopInterface
    {
        $this->shopActive = $active;

        return $this;
    }

    public function setPendingShopSecret(?string $secret): ShopInterface
    {
        $this->pendingShopSecret = $secret;

        return $this;
    }

    public function setPendingShopUrl(?string $shopUrl): ShopInterface
    {
        $this->pendingShopUrl = $shopUrl;

        return $this;
    }

    public function setPreviousShopSecret(string $secret): ShopInterface
    {
        $this->previousShopSecret = $secret;

        return $this;
    }

    public function setSecretsRotatedAt(\DateTimeImmutable $updatedAt): ShopInterface
    {
        $this->secretsRotatedAt = $updatedAt;

        return $this;
    }

    public function isRegistrationConfirmed(): bool
    {
        return $this->registrationConfirmed;
    }

    public function setRegistrationConfirmed(): ShopInterface
    {
        $this->registrationConfirmed = true;

        return $this;
    }

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     */
    public function setVerifiedWithDoubleSignature(): ShopInterface
    {
        $this->hasVerifiedWithDoubleSignature = true;

        return $this;
    }

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     */
    public function hasVerifiedWithDoubleSignature(): bool
    {
        return $this->hasVerifiedWithDoubleSignature;
    }
}
