<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Events;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Event\RegistrationBeforeCompletedEvent;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(RegistrationBeforeCompletedEvent::class)]
#[CoversClass(MockShop::class)]
class RegistrationBeforeCompletedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new RegistrationBeforeCompletedEvent(
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
