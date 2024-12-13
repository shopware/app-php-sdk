<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Storefront;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Storefront\StorefrontClaims;

#[CoversClass(StorefrontClaims::class)]
class StorefrontClaimsTest extends TestCase
{
    public function testAllSet(): void
    {
        $claims = new StorefrontClaims([
            'salesChannelId' => 'salesChannelId',
            'customerId' => 'customerId',
            'currencyId' => 'currencyId',
            'languageId' => 'languageId',
            'paymentMethodId' => 'paymentMethodId',
            'shippingMethodId' => 'shippingMethodId',
            'inAppPurchases' => 'inAppPurchases',
        ]);

        static::assertSame('salesChannelId', $claims->getSalesChannelId());
        static::assertSame('customerId', $claims->getCustomerId());
        static::assertSame('currencyId', $claims->getCurrencyId());
        static::assertSame('languageId', $claims->getLanguageId());
        static::assertSame('paymentMethodId', $claims->getPaymentMethodId());
        static::assertSame('shippingMethodId', $claims->getShippingMethodId());
        static::assertSame('inAppPurchases', $claims->getInAppPurchases());
    }

    public function testMissingSalesChannelId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "salesChannelId"');
        $claims->getSalesChannelId();
    }

    public function testMissingCustomerId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "customerId"');
        $claims->getCustomerId();
    }

    public function testMissingCurrencyId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "currencyId"');
        $claims->getCurrencyId();
    }

    public function testMissingLanguageId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "languageId"');
        $claims->getLanguageId();
    }

    public function testMissingPaymentMethodId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "paymentMethodId"');
        $claims->getPaymentMethodId();
    }

    public function testMissingShippingMethodId(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "shippingMethodId"');
        $claims->getShippingMethodId();
    }

    public function testMissingInAppPurchases(): void
    {
        $claims = new StorefrontClaims([]);

        $this->expectExceptionMessage('Missing claim "inAppPurchases"');
        $claims->getInAppPurchases();
    }
}
