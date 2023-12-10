<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\LineItem;

#[CoversClass(LineItem::class)]
class LineItemTest extends TestCase
{
    public function testConstruct(): void
    {
        $price = new CalculatedPrice(
            [
                'unitPrice' => 1.0,
                'totalPrice' => 1.0,
                'calculatedTaxes' => [],
                'taxRules' => [],
            ]
        );

        $lineItem = new LineItem(
            [
                'uniqueIdentifier' => 'unique-identifier',
                'type' => 'product',
                'referencedId' => 'referenced-id',
                'label' => 'label',
                'good' => true,
                'quantity' => 1,
                'payload' => [
                    'foo' => 'bar',
                ],
                'price' => [
                    'unitPrice' => 1.0,
                    'totalPrice' => 1.0,
                    'calculatedTaxes' => [],
                    'taxRules' => [],
                ],
                'states' => [
                    'foo' => 'bar',
                ],
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

        static::assertSame('unique-identifier', $lineItem->getUniqueIdentifier());
        static::assertSame('product', $lineItem->getType());
        static::assertSame('referenced-id', $lineItem->getReferencedId());
        static::assertSame('label', $lineItem->getLabel());
        static::assertTrue($lineItem->isGood());
        static::assertSame(1, $lineItem->getQuantity());
        static::assertSame(['foo' => 'bar'], $lineItem->getPayload());
        static::assertEquals($price, $lineItem->getPrice());
        static::assertSame(['foo' => 'bar'], $lineItem->getStates());

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
