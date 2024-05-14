<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentRecurringAction;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentRecurringAction::class)]
class PaymentRecurringActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $source = new ActionSource('https://shop-url.com', '1.0.0');
        $order = new Order(['id' => 'order-id']);
        $orderTransaction = new OrderTransaction(['id' => 'order-transaction-id']);

        $action = new PaymentRecurringAction($shop, $source, $order, $orderTransaction);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($order, $action->order);
        static::assertSame($orderTransaction, $action->orderTransaction);
    }
}
