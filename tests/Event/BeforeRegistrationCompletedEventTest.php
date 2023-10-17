<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Event;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Event\BeforeRegistrationCompletedEvent;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(BeforeRegistrationCompletedEvent::class)]
class BeforeRegistrationCompletedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new BeforeRegistrationCompletedEvent(
            new MockShop('shop-id', 'shop-url', 'shop-secret'),
            new Request('GET', 'http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890'),
            ['apiKey' => 'foo', 'secretKey' => 'bar']
        );

        static::assertSame('shop-id', $event->getShop()->getShopId());
        static::assertSame('shop-url', $event->getShop()->getShopUrl());
        static::assertSame('GET', $event->getRequest()->getMethod());
        static::assertSame('http://localhost?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890', (string) $event->getRequest()->getUri());

        static::assertSame(['apiKey' => 'foo', 'secretKey' => 'bar'], $event->getConfirmation());
    }
}
