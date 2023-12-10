<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\CartPrice;
use Shopware\App\SDK\Context\Cart\LineItem;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderCustomer;
use Shopware\App\SDK\Context\Order\OrderDelivery;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;

#[CoversClass(Order::class)]
class OrderTest extends TestCase
{
    public function testConstruct(): void
    {
        $cartPrice = [
            'netPrice' => 1.0,
            'totalPrice' => 1.19,
            'calculatedTaxes' => [],
            'taxStatus' => 'gross',
            'taxRules' => [],
            'positionPrice' => 1.0,
            'rawTotal' => 1.0,
        ];

        $shippingCosts = [
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $orderCustomer = ['id' => 'foo'];
        $currency = ['id' => 'foo'];
        $address = ['id' => 'foo'];

        $lineItems = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $rounding = [
            'decimals' => 1,
            'interval' => 1.0,
            'roundForNet' => true,
            'roundForGross' => true,
        ];

        $deliveries = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $transactions = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $order = new Order([
            'id' => 'order-id',
            'orderNumber' => 'order-number',
            'currencyFactor' => 1.0,
            'orderDateTime' => '2021-01-01T00:00:00+00:00',
            'price' => $cartPrice,
            'amountTotal' => 1.0,
            'amountNet' => 1.0,
            'positionPrice' => 1.0,
            'taxStatus' => 'gross',
            'shippingTotal' => 1.0,
            'shippingCosts' => $shippingCosts,
            'orderCustomer' => $orderCustomer,
            'currency' => $currency,
            'billingAddress' => $address,
            'lineItems' => $lineItems,
            'itemRounding' => $rounding,
            'totalRounding' => $rounding,
            'deepLinkCode' => '123456abcdef',
            'salesChannelId' => 'sales-channel-id',
            'deliveries' => $deliveries,
            'transactions' => $transactions,
        ]);

        static::assertSame('order-id', $order->getId());
        static::assertSame('order-number', $order->getOrderNumber());
        static::assertSame(1.0, $order->getCurrencyFactor());
        static::assertEquals(new \DateTime('2021-01-01T00:00:00+00:00'), $order->getOrderDate());
        static::assertEquals(new CartPrice($cartPrice), $order->getPrice());
        static::assertSame(1.0, $order->getAmountTotal());
        static::assertSame(1.0, $order->getAmountNet());
        static::assertSame(1.0, $order->getPositionPrice());
        static::assertSame('gross', $order->getTaxStatus());
        static::assertSame(1.0, $order->getShippingTotal());
        static::assertEquals(new CalculatedPrice($shippingCosts), $order->getShippingCosts());
        static::assertEquals(new OrderCustomer($orderCustomer), $order->getOrderCustomer());
        static::assertEquals(new Currency($currency), $order->getCurrency());
        static::assertEquals(new Address($address), $order->getBillingAddress());
        static::assertEquals([new LineItem($lineItems[0]), new LineItem($lineItems[1])], $order->getLineItems());
        static::assertEquals(new RoundingConfig($rounding), $order->getItemRounding());
        static::assertEquals(new RoundingConfig($rounding), $order->getTotalRounding());
        static::assertSame('123456abcdef', $order->getDeepLinkCode());
        static::assertSame('sales-channel-id', $order->getSalesChannelId());
        static::assertEquals([new OrderDelivery($deliveries[0]), new OrderDelivery($deliveries[1])], $order->getDeliveries());
        static::assertEquals([new OrderTransaction($transactions[0]), new OrderTransaction($transactions[1])], $order->getTransactions());
    }
}
