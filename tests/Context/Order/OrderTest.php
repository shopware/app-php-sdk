<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\Order;

#[CoversClass(Order::class)]
class OrderTest extends TestCase
{
    public function testConstruct(): void
    {
        $order = new Order([
            'id' => 'order-id',
            'orderNumber' => 'order-number',
            'currencyFactor' => 1.2,
            'orderDateTime' => '2024-01-01T00:00:00+00:00',
            'price' => ['netPrice' => 100.0],
            'amountTotal' => 100.0,
            'amountNet' => 100.0,
            'positionPrice' => 100.0,
            'taxStatus' => 'gross',
            'shippingTotal' => 10.0,
            'shippingCosts' => ['unitPrice' => 10.0],
            'orderCustomer' => ['id' => 'order-customer-id'],
            'currency' => ['id' => 'currency-id'],
            'billingAddress' => ['id' => 'billing-address-id'],
            'lineItems' => [['id' => 'line-item-id']],
            'itemRounding' => ['decimals' => 2],
            'totalRounding' => ['decimals' => 2],
            'deepLinkCode' => 'deep-link-code',
            'salesChannelId' => 'sales-channel-id',
            'deliveries' => [['trackingCodes' => ['tracking-code']]],
            'transactions' => [['id' => 'transaction-id']],
        ]);

        static::assertSame('order-id', $order->getId());
        static::assertSame('order-number', $order->getOrderNumber());
        static::assertSame(1.2, $order->getCurrencyFactor());
        static::assertSame('2024-01-01T00:00:00+00:00', $order->getOrderDate()->format(\DateTime::ATOM));
        static::assertSame(100.0, $order->getPrice()->getNetPrice());
        static::assertSame(100.0, $order->getAmountTotal());
        static::assertSame(100.0, $order->getAmountNet());
        static::assertSame(100.0, $order->getPositionPrice());
        static::assertSame('gross', $order->getTaxStatus());
        static::assertSame(10.0, $order->getShippingTotal());
        static::assertSame(10.0, $order->getShippingCosts()->getUnitPrice());
        static::assertSame('order-customer-id', $order->getOrderCustomer()->getId());
        static::assertSame('currency-id', $order->getCurrency()->getId());
        static::assertSame('billing-address-id', $order->getBillingAddress()->getId());
        static::assertCount(1, $order->getLineItems());
        static::assertSame('line-item-id', $order->getLineItems()->first()?->getId());
        static::assertSame(2, $order->getItemRounding()->getDecimals());
        static::assertSame(2, $order->getTotalRounding()->getDecimals());
        static::assertSame('deep-link-code', $order->getDeepLinkCode());
        static::assertSame('sales-channel-id', $order->getSalesChannelId());
        static::assertCount(1, $order->getDeliveries());
        static::assertSame(['tracking-code'], $order->getDeliveries()->first()?->getTrackingCodes());
        static::assertCount(1, $order->getTransactions());
        static::assertSame('transaction-id', $order->getTransactions()->first()?->getId());
    }
}
