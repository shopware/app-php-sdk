<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\OrderDelivery;
use Shopware\App\SDK\Context\Order\StateMachineState;
use Shopware\App\SDK\Context\SalesChannelContext\Address;

#[CoversClass(OrderDelivery::class)]
class OrderDeliveryTest extends TestCase
{
    public function testConstruct(): void
    {
        $shippingCosts = [
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $address = ['id' => 'foo'];
        $stateMachineState = ['id' => 'foo', 'technicalName' => 'test_foo'];

        $orderDelivery = new OrderDelivery([
            'trackingCodes' => ['foo', 'bar'],
            'shippingCosts' => $shippingCosts,
            'shippingOrderAddress' => $address,
            'stateMachineState' => $stateMachineState,
            'shippingDateEarliest' => '2021-01-01T00:00:00+00:00',
            'shippingDateLatest' => '2021-01-01T00:00:00+00:00',
        ]);

        static::assertSame(['foo', 'bar'], $orderDelivery->getTrackingCodes());
        static::assertEquals(new CalculatedPrice($shippingCosts), $orderDelivery->getShippingCosts());
        static::assertEquals(new Address($address), $orderDelivery->getShippingOrderAddress());
        static::assertEquals(new StateMachineState($stateMachineState), $orderDelivery->getStateMachineState());
        static::assertEquals(new \DateTime('2021-01-01T00:00:00+00:00'), $orderDelivery->getShippingDateEarliest());
        static::assertEquals(new \DateTime('2021-01-01T00:00:00+00:00'), $orderDelivery->getShippingDateLatest());
    }
}
