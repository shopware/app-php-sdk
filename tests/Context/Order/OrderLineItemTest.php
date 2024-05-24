<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\OrderLineItem;

#[CoversClass(OrderLineItem::class)]
class OrderLineItemTest extends TestCase
{
    public function testConstruct(): void
    {
        $orderLineItem = new OrderLineItem([
            'parentId' => 'parent-id',
            'position' => 1,
        ]);

        static::assertSame('parent-id', $orderLineItem->getParentId());
        static::assertSame(1, $orderLineItem->getPosition());
    }
}
