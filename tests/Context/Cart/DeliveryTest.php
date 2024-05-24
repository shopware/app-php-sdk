<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Delivery;

#[CoversClass(Delivery::class)]
class DeliveryTest extends TestCase
{
    public function testConstruct(): void
    {
        $delivery = new Delivery([
            'positions' => [
                [
                    'identifier' => 'bar',
                ],
            ],
            'location' => [
                'country' => [
                    'name' => 'bar',
                ],
            ],
            'shippingMethod' => [
                'id' => 'bar',
            ],
            'deliveryDate' => [
                'earliest' => '2024-01-01T00:00:00+00:00',
            ],
            'shippingCosts' => [
                'unitPrice' => 100.0,
            ],
        ]);

        static::assertCount(1, $delivery->getPositions());
        static::assertSame('bar', $delivery->getPositions()->first()?->getIdentifier());
        static::assertSame('bar', $delivery->getLocation()->getCountry()->getName());
        static::assertSame('bar', $delivery->getShippingMethod()->getId());
        static::assertSame('2024-01-01T00:00:00+00:00', $delivery->getDeliveryDate()->getEarliest()->format(\DateTime::ATOM));
        static::assertSame(100.0, $delivery->getShippingCosts()->getUnitPrice());
    }
}
