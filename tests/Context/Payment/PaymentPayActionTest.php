<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\Payment\RecurringData;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentPayAction::class)]
class PaymentPayActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $order = new Order([]);
        $orderTransaction = new OrderTransaction([]);
        $returnUrl = 'https://example.com/return-url';
        $recurring = new RecurringData([]);
        $requestData = ['foo' => 'bar'];

        $action = new PaymentPayAction(
            $shop,
            $source,
            $order,
            $orderTransaction,
            $returnUrl,
            $recurring,
            $requestData
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertSame($returnUrl, $action->returnUrl);
        static::assertSame($recurring, $action->recurring);
        static::assertSame($requestData, $action->requestData);
    }

    public function testConstructWithDefaults(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $order = new Order([]);
        $orderTransaction = new OrderTransaction([]);

        $action = new PaymentPayAction(
            $shop,
            $source,
            $order,
            $orderTransaction,
            null
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertNull($action->returnUrl);
        static::assertNull($action->recurring);
        static::assertSame([], $action->requestData);
    }
}
