<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\Storefront;

use Shopware\App\SDK\Exception\MissingClaimException;

/**
 * @psalm-type StorefrontClaimsArray = array{
 *     salesChannelId?: string,
 *     customerId?: string,
 *     currencyId?: string,
 *     languageId?: string,
 *     paymentMethodId?: string,
 *     shippingMethodId?: string,
 *     inAppPurchases?: string[],
 * }
 */
class StorefrontClaims
{
    /**
     * @param StorefrontClaimsArray $claims
     */
    public function __construct(private readonly array $claims)
    {
    }

    public function getSalesChannelId(): string
    {
        $value = $this->claims['salesChannelId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('salesChannelId');
        }

        return $value;
    }

    public function getCustomerId(): string
    {
        $value = $this->claims['customerId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('customerId');
        }

        return $value;
    }

    public function getCurrencyId(): string
    {
        $value = $this->claims['currencyId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('currencyId');
        }

        return $value;
    }

    public function getLanguageId(): string
    {
        $value = $this->claims['languageId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('languageId');
        }

        return $value;
    }

    public function getPaymentMethodId(): string
    {
        $value = $this->claims['paymentMethodId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('paymentMethodId');
        }

        return $value;
    }

    public function getShippingMethodId(): string
    {
        $value = $this->claims['shippingMethodId'] ?? null;
        if (!is_string($value)) {
            throw new MissingClaimException('shippingMethodId');
        }

        return $value;
    }

    /**
     * @return string[]
     */
    public function getInAppPurchases(): array
    {
        $value = $this->claims['inAppPurchases'] ?? [];
        if (!\is_array($value)) {
            throw new MissingClaimException('inAppPurchases');
        }

        return $value;
    }

    public function hasInAppPurchase(string $inAppPurchase): bool
    {
        return \in_array($inAppPurchase, $this->getInAppPurchases(), true);
    }
}
