<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(MockShop::class)]
class MockShopTest extends TestCase
{
    public function testConstructWithDefaults(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');

        static::assertSame('shop-id', $shop->getShopId());
        static::assertSame('https://example.com', $shop->getShopUrl());
        static::assertSame('shop-secret', $shop->getShopSecret());
        static::assertFalse($shop->isShopActive());
        static::assertNull($shop->getShopClientId());
        static::assertNull($shop->getShopClientSecret());
    }

    public function testConstructWithCustomValues(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret', true, 'client-id', 'client-secret');

        static::assertSame('shop-id', $shop->getShopId());
        static::assertSame('https://example.com', $shop->getShopUrl());
        static::assertSame('shop-secret', $shop->getShopSecret());
        static::assertTrue($shop->isShopActive());
        static::assertSame('client-id', $shop->getShopClientId());
        static::assertSame('client-secret', $shop->getShopClientSecret());
    }

    public function testSetShopApiCredentials(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');

        $shop->setShopApiCredentials('client-id', 'client-secret');

        static::assertSame('client-id', $shop->getShopClientId());
        static::assertSame('client-secret', $shop->getShopClientSecret());
    }

    public function testSetShopUrl(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');

        $shop->setShopUrl('https://example.org');

        static::assertSame('https://example.org', $shop->getShopUrl());
    }

    public function testSetShopActive(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');

        $shop->setShopActive(true);

        static::assertTrue($shop->isShopActive());
    }
}
