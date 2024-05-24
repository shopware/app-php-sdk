<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Order\OrderTransaction;

#[CoversClass(OrderTransaction::class)]
class OrderTransactionTest extends TestCase
{
    public function testConstruct(): void
    {
        $orderTransaction = new OrderTransaction([
            'id' => 'transaction-id',
            'amount' => [
                'unitPrice' => 10.0,
            ],
            'paymentMethod' => [
                'name' => 'paypal',
            ],
            'stateMachineState' => [
                'technicalName' => 'open',
            ],
            'order' => [
                'id' => 'order-id',
            ],
            'customFields' => [
                'key' => 'value',
            ],
        ]);

        static::assertSame('transaction-id', $orderTransaction->getId());
        static::assertSame(10.0, $orderTransaction->getAmount()->getUnitPrice());
        static::assertSame('paypal', $orderTransaction->getPaymentMethod()->getName());
        static::assertSame('open', $orderTransaction->getStateMachineState()->getTechnicalName());
        static::assertSame('order-id', $orderTransaction->getOrder()->getId());
        static::assertArrayHasKey('key', $orderTransaction->getCustomFields());
        static::assertSame('value', $orderTransaction->getCustomFields()['key']);
    }
}
