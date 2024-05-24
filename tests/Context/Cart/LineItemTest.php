<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\LineItem;

#[CoversClass(LineItem::class)]
class LineItemTest extends TestCase
{
    public function testConstruct(): void
    {
        $lineItem = new LineItem(
            [
                'id' => 'foo',
                'uniqueIdentifier' => 'bar',
                'type' => 'baz',
                'referencedId' => 'qux',
                'label' => 'quux',
                'description' => 'quuz',
                'good' => true,
                'quantity' => 42,
                'payload' => ['foo' => 'bar'],
                'price' => [
                    'unitPrice' => 10.0,
                    'quantity' => 2,
                    'totalPrice' => 20.0,
                    'calculatedTaxes' => [
                        [
                            'taxRate' => 19.0,
                            'price' => 10.0,
                            'tax' => 1.9,
                        ],
                    ],
                    'taxRules' => [
                        [
                            'taxRate' => 19.0,
                            'percentage' => 100.0,
                        ],
                    ],
                ],
                'states' => [
                    'foo' => 'bar',
                ],
            ]
        );

        static::assertSame('foo', $lineItem->getId());
        static::assertSame('bar', $lineItem->getUniqueIdentifier());
        static::assertSame('baz', $lineItem->getType());
        static::assertSame('qux', $lineItem->getReferencedId());
        static::assertSame('quux', $lineItem->getLabel());
        static::assertSame('quuz', $lineItem->getDescription());
        static::assertTrue($lineItem->isGood());
        static::assertSame(42, $lineItem->getQuantity());
        static::assertSame(['foo' => 'bar'], $lineItem->getPayload());
        static::assertSame(10.0, $lineItem->getPrice()->getUnitPrice());
        static::assertSame(2, $lineItem->getPrice()->getQuantity());
        static::assertSame(20.0, $lineItem->getPrice()->getTotalPrice());
        static::assertCount(1, $lineItem->getPrice()->getCalculatedTaxes());
        static::assertCount(1, $lineItem->getPrice()->getTaxRules());
        static::assertSame(['foo' => 'bar'], $lineItem->getStates());
    }

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
        static::assertInstanceOf(LineItem::class, $children->first());
        static::assertInstanceOf(LineItem::class, $children->last());

        static::assertSame('foo', $children->first()->getId());
        static::assertTrue($children->first()->isGood());

        static::assertSame('bar', $children->last()->getId());
        static::assertFalse($children->last()->isGood());
    }
}
