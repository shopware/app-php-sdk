<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\OrderDelivery;

#[CoversClass(OrderDelivery::class)]
class OrderDeliveryTest extends TestCase
{
    public function testConstruct(): void
    {
        $orderDelivery = new OrderDelivery([
            'trackingCodes' => ['foo', 'bar'],
            'shippingCosts' => ['unitPrice' => 100.0],
            'shippingOrderAddress' => ['city' => 'bar'],
            'stateMachineState' => ['technicalName' => 'bar'],
            'shippingDateEarliest' => '2024-01-01T00:00:00+00:00',
            'shippingDateLatest' => '2024-01-01T00:00:00+00:00',
        ]);

        static::assertSame(['foo', 'bar'], $orderDelivery->getTrackingCodes());
        static::assertSame(100.0, $orderDelivery->getShippingCosts()->getUnitPrice());
        static::assertSame('bar', $orderDelivery->getShippingOrderAddress()->getCity());
        static::assertSame('bar', $orderDelivery->getStateMachineState()->getTechnicalName());
        static::assertSame('2024-01-01T00:00:00+00:00', $orderDelivery->getShippingDateEarliest()->format(\DateTime::ATOM));
        static::assertSame('2024-01-01T00:00:00+00:00', $orderDelivery->getShippingDateLatest()->format(\DateTime::ATOM));
    }
}
