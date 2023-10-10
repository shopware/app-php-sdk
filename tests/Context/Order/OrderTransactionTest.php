<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Order\StateMachineState;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;

#[CoversClass(OrderTransaction::class)]
class OrderTransactionTest extends TestCase
{
    public function testConstruct(): void
    {
        $amount = [
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $paymentMethod = ['id' => 'foo'];
        $stateMachineState = ['id' => 'foo', 'technicalName' => 'test_foo'];

        $orderTransaction = new OrderTransaction([
            'id' => 'foo',
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
            'stateMachineState' => $stateMachineState,
        ]);

        static::assertSame('foo', $orderTransaction->getId());
        static::assertEquals(new CalculatedPrice($amount), $orderTransaction->getAmount());
        static::assertEquals(new PaymentMethod($paymentMethod), $orderTransaction->getPaymentMethod());
        static::assertEquals(new StateMachineState($stateMachineState), $orderTransaction->getStateMachineState());
    }
}
