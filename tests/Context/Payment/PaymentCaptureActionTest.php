<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\RecurringData;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentCaptureAction::class)]
class PaymentCaptureActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $order = new Order([]);
        $orderTransaction = new OrderTransaction([]);
        $recurring = new RecurringData([]);
        $requestData = ['foo' => 'bar'];

        $action = new PaymentCaptureAction(
            $shop,
            $source,
            $order,
            $orderTransaction,
            $recurring,
            $requestData
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertSame($recurring, $action->recurring);
        static::assertSame($requestData, $action->requestData);
    }

    public function testConstructWithDefaults(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $order = new Order([]);
        $orderTransaction = new OrderTransaction([]);

        $action = new PaymentCaptureAction(
            $shop,
            $source,
            $order,
            $orderTransaction,
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertNull($action->recurring);
        static::assertSame([], $action->requestData);
    }
}
