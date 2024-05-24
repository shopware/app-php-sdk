<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\DeliveryPosition;

#[CoversClass(DeliveryPosition::class)]
class DeliveryPositionTest extends TestCase
{
    public function testConstruct(): void
    {
        $deliveryPosition = new DeliveryPosition([
            'identifier' => 'bar',
            'lineItem' => ['id' => 'foo'],
            'quantity' => 1,
            'deliveryDate' => ['earliest' => '2024-01-01T00:00:00+00:00', 'latest' => '2024-01-02T00:00:00+00:00'],
            'price' => ['unitPrice' => 100.0],
        ]);

        static::assertSame('bar', $deliveryPosition->getIdentifier());
        static::assertSame('foo', $deliveryPosition->getLineItem()->getId());
        static::assertSame(1, $deliveryPosition->getQuantity());
        static::assertSame('2024-01-01T00:00:00+00:00', $deliveryPosition->getDeliveryDate()->getEarliest()->format(\DateTime::ATOM));
        static::assertSame('2024-01-02T00:00:00+00:00', $deliveryPosition->getDeliveryDate()->getLatest()->format(\DateTime::ATOM));
        static::assertSame(100.0, $deliveryPosition->getPrice()->getUnitPrice());
    }
}
