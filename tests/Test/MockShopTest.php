<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(MockShop::class)]
class MockShopTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop(
            'shop-id',
            'shop-url',
            'shop-secret',
            true,
            'client-id',
            'client-secret',
        );

        static::assertSame('shop-id', $shop->getShopId());
        static::assertSame('shop-url', $shop->getShopUrl());
        static::assertSame('shop-secret', $shop->getShopSecret());
        static::assertSame('client-id', $shop->getShopClientId());
        static::assertSame('client-secret', $shop->getShopClientSecret());
        static::assertTrue($shop->isShopActive());
    }

    public function testDefaults(): void
    {
        $shop = new MockShop(
            'shop-id',
            'shop-url',
            'shop-secret',
        );

        static::assertNull($shop->getShopClientId());
        static::assertNull($shop->getShopClientSecret());
        static::assertFalse($shop->isShopActive());
    }

    public function testSetters(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret', true);

        $shop->setShopApiCredentials('new-client-id', 'new-client-secret');
        $shop->setShopUrl('new-shop-url');
        $shop->setShopActive(false);

        static::assertSame('new-shop-url', $shop->getShopUrl());
        static::assertSame('new-client-id', $shop->getShopClientId());
        static::assertSame('new-client-secret', $shop->getShopClientSecret());
        static::assertFalse($shop->isShopActive());
    }
}
