<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Shop;

interface ShopInterface
{
    /**
     * Contains the state is an app active in the shop
     */
    public function isShopActive(): bool;

    public function getShopId(): string;

    public function getShopUrl(): string;

    public function getPendingShopUrl(): ?string;

    public function getShopSecret(): string;

    public function getPreviousShopSecret(): ?string;

    public function getShopClientId(): ?string;

    public function getShopClientSecret(): ?string;

    public function setShopApiCredentials(string $clientId, string $clientSecret): ShopInterface;

    public function setShopUrl(string $url): ShopInterface;

    public function setShopActive(bool $active): ShopInterface;

    public function setShopSecret(string $secret): ShopInterface;

    public function getPendingShopSecret(): ?string;

    public function setPendingShopSecret(?string $secret): ShopInterface;

    public function setPendingShopUrl(?string $shopUrl): ShopInterface;

    public function setPreviousShopSecret(string $secret): ShopInterface;

    public function setSecretsRotatedAt(\DateTimeImmutable $updatedAt): ShopInterface;

    public function getSecretsRotatedAt(): ?\DateTimeImmutable;

    /**
     * Indicates whether at least one registration confirmation has been completed.
     */
    public function isRegistrationConfirmed(): bool;

    public function setRegistrationConfirmed(): ShopInterface;

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     */
    public function setVerifiedWithDoubleSignature(): ShopInterface;

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     */
    public function hasVerifiedWithDoubleSignature(): bool;

}
