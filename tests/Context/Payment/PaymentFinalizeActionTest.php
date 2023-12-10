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
    public function testConstruct(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $orderTransaction = new OrderTransaction([]);
        $recurring = new RecurringData([]);
        $queryParameters = ['foo' => 'bar'];

        $action = new PaymentFinalizeAction(
            $shop,
            $source,
            $orderTransaction,
            $recurring,
            $queryParameters
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertSame($recurring, $action->recurring);
        static::assertSame($queryParameters, $action->queryParameters);
    }

    public function testConstructWithDefaults(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $orderTransaction = new OrderTransaction([]);

        $action = new PaymentFinalizeAction(
            $shop,
            $source,
            $orderTransaction,
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($orderTransaction, $action->orderTransaction);
        static::assertNull($action->recurring);
        static::assertSame([], $action->queryParameters);
    }
}
