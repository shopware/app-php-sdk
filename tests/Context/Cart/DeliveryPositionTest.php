<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\DeliveryDate;
use Shopware\App\SDK\Context\Cart\DeliveryPosition;
use Shopware\App\SDK\Context\Cart\LineItem;

#[CoversClass(DeliveryPosition::class)]
class DeliveryPositionTest extends TestCase
{
    public function testConstruct(): void
    {
        $lineItem = [
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
        ];

        $deliveryDate = [
            'earliest' => '2021-01-01T00:00:00+00:00',
            'latest' => '2021-01-01T00:00:00+00:00',
        ];

        $price = [
            'unitPrice' => 1.0,
            'totalPrice' => 1.0,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $position = new DeliveryPosition(
            [
                'identifier' => 'identifier-1',
                'lineItem' => $lineItem,
                'quantity' => 1,
                'deliveryDate' => $deliveryDate,
                'price' => $price,
            ]
        );

        static::assertSame('identifier-1', $position->getIdentifier());
        static::assertEquals(new LineItem($lineItem), $position->getLineItem());
        static::assertSame(1, $position->getQuantity());
        static::assertEquals(new DeliveryDate($deliveryDate), $position->getDeliveryDate());
        static::assertEquals(new CalculatedPrice($price), $position->getPrice());
    }
}
