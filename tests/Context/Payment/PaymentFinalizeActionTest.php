<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentFinalizeAction;
use Shopware\App\SDK\Context\Payment\RecurringData;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentFinalizeAction::class)]
class PaymentFinalizeActionTest extends TestCase
{
    public function testConstructDefault(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $source = new ActionSource('https://shop-url.com', '1.0.0');
        $orderTransaction = new OrderTransaction(['id' => 'order-transaction-id']);

        $action = new PaymentFinalizeAction($shop, $source, $orderTransaction);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertNull($action->recurring);
        static::assertSame([], $action->queryParameters);
    }

    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $source = new ActionSource('https://shop-url.com', '1.0.0');
        $orderTransaction = new OrderTransaction(['id' => 'order-transaction-id']);
        $recurring = new RecurringData(['subscriptionId' => 'recurring-id']);
        $queryParameters = ['foo' => 'bar'];

        $action = new PaymentFinalizeAction($shop, $source, $orderTransaction, $recurring, $queryParameters);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertSame($recurring, $action->recurring);
        static::assertSame($queryParameters, $action->queryParameters);
    }
}
