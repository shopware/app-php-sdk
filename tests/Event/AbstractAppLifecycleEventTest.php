<?php

declare(strict_types=1);

namespace Event;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Event\AbstractAppLifecycleEvent;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(AbstractAppLifecycleEvent::class)]
class AbstractAppLifecycleEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $request = new Request('GET', 'https://example.com');
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');

        $event = new class ($request, $shop) extends AbstractAppLifecycleEvent {};

        static::assertSame($request, $event->getRequest());
        static::assertSame($shop, $event->getShop());
    }
}
