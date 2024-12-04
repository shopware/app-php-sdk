<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\RecurringData;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentCaptureAction::class)]
class PaymentCaptureActionTest extends TestCase
{
    public function testConstructDefault(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $IAPs = new Collection([new InAppPurchase('id', 1)]);
        $source = new ActionSource('https://shop-url.com', '1.0.0', $IAPs);
        $order = new Order(['id' => 'order-id']);
        $orderTransaction = new OrderTransaction(['id' => 'order-transaction-id']);

        $action = new PaymentCaptureAction($shop, $source, $order, $orderTransaction);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertNull($action->recurring);
        static::assertSame([], $action->requestData);
        static::assertSame($IAPs, $action->source->inAppPurchases);
    }

    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $IAPs = new Collection([new InAppPurchase('id', 1)]);
        $source = new ActionSource('https://shop-url.com', '1.0.0', $IAPs);
        $order = new Order(['id' => 'order-id']);
        $orderTransaction = new OrderTransaction(['id' => 'order-transaction-id']);
        $recurring = new RecurringData(['subscriptionId' => 'recurring-id']);
        $requestData = ['foo' => 'bar'];

        $action = new PaymentCaptureAction($shop, $source, $order, $orderTransaction, $recurring, $requestData);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertSame($recurring, $action->recurring);
        static::assertSame($requestData, $action->requestData);
        static::assertSame($IAPs, $action->source->inAppPurchases);
    }
}
