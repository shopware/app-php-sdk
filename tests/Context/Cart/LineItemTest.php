<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\LineItem;

#[CoversClass(LineItem::class)]
#[CoversClass(ArrayStruct::class)]
class LineItemTest extends TestCase
{
    public function testGetChildren(): void
    {
        $lineItem = new LineItem(
            [
                'children' => [
                    [
                        'id' => 'foo',
                        'good' => true,
                    ],
                    [
                        'id' => 'bar',
                        'good' => false,
                    ],
                ],
            ]
        );

        $children = $lineItem->getChildren();

        static::assertCount(2, $children);
        static::assertInstanceOf(LineItem::class, $children[0]);
        static::assertInstanceOf(LineItem::class, $children[1]);

        static::assertSame('foo', $children[0]->getId());
        static::assertTrue($children[0]->isGood());

        static::assertSame('bar', $children[1]->getId());
        static::assertFalse($children[1]->isGood());
    }
}
