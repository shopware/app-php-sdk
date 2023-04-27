<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Events;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Event\RegistrationCompletedEvent;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(RegistrationCompletedEvent::class)]
#[CoversClass(MockShop::class)]
class RegistrationCompletedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new RegistrationCompletedEvent(
            new MockShop('shop-id', 'shop-url', 'shop-secret'),
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890'),
        );

        static::assertSame('shop-id', $event->getShop()->getShopId());
        static::assertSame('shop-url', $event->getShop()->getShopUrl());
        static::assertSame('GET', $event->getRequest()->getMethod());
        static::assertSame('http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890', (string) $event->getRequest()->getUri());

    }
}