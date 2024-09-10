<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Adapter\DynamoDB;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Adapter\DynamoDB\DynamoDBShop;
use PHPUnit\Framework\TestCase;

#[CoversClass(DynamoDBShop::class)]
class DynamoDBShopTest extends TestCase
{
    public function testStruct(): void
    {
        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'shopClientId', 'shopClientSecret', true);

        static::assertSame('shopId', $shop->getShopId());
        static::assertSame('shopUrl', $shop->getShopUrl());
        static::assertSame('shopSecret', $shop->getShopSecret());
        static::assertSame('shopClientId', $shop->getShopClientId());
        static::assertSame('shopClientSecret', $shop->getShopClientSecret());
        static::assertTrue($shop->isShopActive());

        $shop->setShopUrl('newShopUrl');

        static::assertSame('newShopUrl', $shop->getShopUrl());

        $shop->setShopActive(false);

        static::assertFalse($shop->isShopActive());

        $shop->setShopApiCredentials('newClientId', 'newClientSecret');

        static::assertSame('newClientId', $shop->getShopClientId());
        static::assertSame('newClientSecret', $shop->getShopClientSecret());

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret');

        static::assertFalse($shop->isShopActive());
    }
}
