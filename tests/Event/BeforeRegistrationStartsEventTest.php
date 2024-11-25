<?php

declare(strict_types=1);

namespace Event;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Event\BeforeRegistrationStartsEvent;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(BeforeRegistrationStartsEvent::class)]
class BeforeRegistrationStartsEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new BeforeRegistrationStartsEvent(
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890'),
            new MockShop('shop-id', 'shop-url', 'shop-secret'),
        );

        static::assertSame('GET', $event->getRequest()->getMethod());
        static::assertSame('http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890', (string) $event->getRequest()->getUri());
        static::assertSame('shop-id', $event->getShop()->getShopId());
        static::assertSame('shop-url', $event->getShop()->getShopUrl());
        static::assertSame('shop-secret', $event->getShop()->getShopSecret());
    }
}
